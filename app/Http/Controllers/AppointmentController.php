<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    /**
     * Display the appointment scheduling page with appointments for the selected date.
     */
    public function index(Request $request)
    {
        $selectedDate = $request->input('appointment_date', date('Y-m-d'));

        $appointments = DB::select("
            SELECT
                a.appointment_id,
                a.patient_no,
                p.first_name || ' ' || p.last_name AS patient_name,
                a.staff_no,
                s.first_name || ' ' || s.last_name AS staff_name,
                a.appointment_date,
                a.appointment_time,
                a.examination_room,
                a.appointment_type,
                a.status
            FROM appointments a
            LEFT JOIN patients p ON a.patient_no = p.patient_no
            LEFT JOIN staff s ON a.staff_no = s.staff_no
            WHERE a.appointment_date = ?
            AND a.status NOT IN ('Completed', 'Cancelled')
            ORDER BY a.appointment_time ASC
        ", [$selectedDate]);

        $patients = DB::table('patients')
            ->select('patient_no', 'first_name', 'last_name')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $staff = DB::table('staff')
            ->select('staff_no', 'first_name', 'last_name')
            ->where('position', 'Doctor')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $summaryRow = DB::select("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'Checked In' THEN 1 ELSE 0 END) AS checked_in
            FROM appointments
            WHERE appointment_date = ?
            AND status NOT IN ('Completed', 'Cancelled')
        ", [$selectedDate])[0];

        $summary = [
            ['label' => 'Total Appointments', 'value' => $summaryRow->total],
            ['label' => 'Confirmed',          'value' => $summaryRow->confirmed],
            ['label' => 'Pending',            'value' => $summaryRow->pending],
            ['label' => 'Checked In',         'value' => $summaryRow->checked_in],
        ];

        return view('module4.appointment', compact('appointments', 'patients', 'staff', 'summary', 'selectedDate'));
    }

    /**
     * Schedule a new appointment using fn_schedule_appointment.
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'patient_no'       => 'required|exists:patients,patient_no',
            'staff_no'         => 'required|exists:staff,staff_no',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'examination_room' => 'nullable|max:100',
            'appointment_type' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        try {
            $result = DB::select('SELECT fn_schedule_appointment(?, ?, ?, ?, ?, ?) AS result', [
                $request->patient_no,
                $request->staff_no,
                $request->appointment_date,
                $request->appointment_time,
                $request->examination_room ?? null,
                $request->appointment_type,
            ]);

            return redirect()->route('module4.appointments', ['appointment_date' => $request->appointment_date])
                ->with('success', 'Appointment scheduled successfully.');
        } catch (\Exception $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'already has an active appointment')) {
                preg_match('/Patient (\w+) already has an active appointment on ([\d-]+) at ([\d:]+)/', $message, $matches);
                $patientNo = $matches[1] ?? '';
                $date      = $matches[2] ?? '';
                $time      = $matches[3] ?? '';
                $userMessage = "Patient {$patientNo} already has an active appointment on {$date} at {$time}. Please choose a different time.";
            } elseif (str_contains($message, 'already has an appointment')) {
                $userMessage = 'This patient already has an appointment at the same date and time. Please choose a different time.';
            } else {
                $userMessage = 'Could not schedule appointment. Please try again.';
            }

            return redirect()->back()->withInput()
                ->with('error', $userMessage);
        }
    }

    /**
     * Cancel an appointment using fn_cancel_appointment.
     */
    public function cancel($id)
    {
        try {
            DB::select('SELECT fn_cancel_appointment(?) AS result', [(int) $id]);

            return redirect()->route('module4.appointments')
                ->with('success', 'Appointment cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Could not cancel appointment: ' . $e->getMessage());
        }
    }

    /**
     * Update the status of an appointment using fn_update_appointment_status.
     */
    public function updateStatus(Request $request, $appointment_id)
    {
        try {
            DB::select('SELECT fn_update_appointment_status(?, ?) AS result', [
                (int) $appointment_id,
                $request->status,
            ]);

            return redirect()->back()
                ->with('success', 'Appointment status updated to ' . $request->status);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Could not update status: ' . $e->getMessage());
        }
    }
}

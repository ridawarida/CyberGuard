<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Incident;

class IncidentWizardController extends Controller
{
    // STEP 1 - Show form: incident date, platform, category
    public function step1()
    {
        $categories = DB::table('behavior_categories')->get();
        return view('incident.step1', ['categories' => $categories]);
    }

    // STEP 1 - Save and move to step 2
    public function postStep1(Request $request)
    {
        $validated = $request->validate([
            'incident_date' => 'required|date',
            'platform' => 'required|string|max:100',
            'behavior_type' => 'required|string|max:100',
        ]);
        session()->forget([
            'incident_wizard.redacted_image',
            'incident_wizard.tracking_id',
        ]);
        session([
            'incident_wizard.incident_date' => $validated['incident_date'],
            'incident_wizard.platform' => $validated['platform'],
            'incident_wizard.behavior_type' => $validated['behavior_type'],
        ]);
        return redirect()->route('incident.wizard.step2');
    }

    // STEP 2 - Show form: description + overview
    public function step2()
    {
        if (!session('incident_wizard.platform')) {
            return redirect()->route('incident.wizard.step1');
        }
        return view('incident.step2');
    }

    // STEP 2 - Save and move to step 3
    public function postStep2(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:2000',
            'overview' => 'nullable|string|max:500',
        ]);
        session([
            'incident_wizard.description' => $validated['description'],
            'incident_wizard.overview' => $validated['overview'] ?? null,
        ]);
        return redirect()->route('incident.wizard.step3');
    }

    // STEP 3 - Show form: screenshot upload
    public function step3()
    {
        if (!session('incident_wizard.description')) {
            return redirect()->route('incident.wizard.step1');
        }
        return view('incident.step3');
    }

    // STEP 3 - Save everything to database
    public function postStep3(Request $request)
    {
        $request->validate([
            'evidence_image' => 'nullable|image|max:5120', // max 5MB
            'email' => 'nullable|email',
        ]);

        $trackingId = 'inc' . strtoupper(Str::random(10));

        $imagePath = null;

        if (session('incident_wizard.redacted_image')) {

             $redactedImage = session('incident_wizard.redacted_image');

            // Remove base64 prefix
             $redactedImage = preg_replace(
             '/^data:image\/\w+;base64,/',
             '',
             $redactedImage
             );

             // Convert base64 to binary
             $redactedImage = base64_decode($redactedImage);

             // Generate a unique filename
             $fileName = 'evidence/' . Str::random(20) . '.png';

             // Save redacted image
             Storage::disk('public')->put(
             $fileName,
             $redactedImage
             );

             $imagePath = $fileName;

        } elseif ($request->hasFile('evidence_image')) {

    // Save original image if no redaction was used
    $imagePath = $request
        ->file('evidence_image')
        ->store('evidence', 'public');
        }

        Incident::create([
            'tracking_id' => $trackingId,
            'platform' => session('incident_wizard.platform'),
            'region' => 'Unknown',
            'description' => session('incident_wizard.description'),
            'incident_date' => session('incident_wizard.incident_date'),
            'behavior_type' => session('incident_wizard.behavior_type'),
            'severity' => 'Unassigned',
            'overview' => session('incident_wizard.overview'),
            'evidence_image' => $imagePath,
            'status' => 'New',
        ]);

        // Send tracking code via email if provided (email is never stored)
        if ($request->filled('email')) {
            $this->sendTrackingCodeEmail($request->input('email'), $trackingId);
        }

        // Clear wizard session data
        session()->forget([
            'incident_wizard.incident_date',
            'incident_wizard.platform',
            'incident_wizard.behavior_type',
            'incident_wizard.description',
            'incident_wizard.overview',
            'incident_wizard.redacted_image',
        ]);

        session(['incident_wizard.tracking_id' => $trackingId]);

        return redirect()->route('incident.wizard.success');
    }

    // Send tracking code via email (email is not stored anywhere)
    private function sendTrackingCodeEmail(string $email, string $trackingId): void
    {
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your report has been submitted successfully.\n\nYour tracking code is: {$trackingId}\n\nPlease keep this code safe — it is the only way to reference your case.",
                function ($message) use ($email) {
                    $message->to($email)
                            ->subject('Your CyberGuard Tracking Code');
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Email sending failed: ' . $e->getMessage());
        }
    }

    // SUCCESS - Show tracking code
    public function success()
    {
        $trackingId = session('incident_wizard.tracking_id');
        if (!$trackingId) {
            return redirect()->route('incident.wizard.step1');
        }
        return view('incident.success', ['tracking_id' => $trackingId]);
    }
    public function redact()
    {
        return view('incident.redact');
    }

    public function postRedact(Request $request)
    {
        session([
            'incident_wizard.redacted_image' => $request->redacted_image
        ]);

        return redirect()->route('incident.wizard.step3');
    }
}
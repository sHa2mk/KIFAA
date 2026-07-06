<?php

namespace App\Http\Controllers;

use App\Services\DigitalTwinService;
use Illuminate\Http\Request;

class DigitalTwinController extends Controller
{
    /**
     * Creates a new Digital Twin controller instance.
     *
     * @param  \App\Services\DigitalTwinService  $digitalTwinService
     * @return void
     */
    public function __construct(
        private DigitalTwinService $digitalTwinService
    ) {}

    /**
     * Shows the main Digital Twin dashboard.
     *
     * This method retrieves the authenticated user and delegates dashboard
     * data preparation to the DigitalTwinService. The controller only returns
     * the prepared data to the dashboard view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $data = $this->digitalTwinService->getDashboardData($user);

        return view('dashboard', $data);
    }

    /**
     * Marks a course skill as completed and updates the user's Digital Twin.
     *
     * The request validates the completed skill ID, then delegates the update
     * process to the DigitalTwinService. The response is returned as JSON
     * because the action is triggered from the dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function completeCourse(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'skill_id' => 'required|exists:skills,id',
        ]);

        $data = $this->digitalTwinService->completeCourse($user, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Course marked as completed successfully.',
            'data' => $data,
        ]);
    }

    /**
     * Simulates the effect of learning one missing skill on readiness score.
     *
     * This method sends the selected skill name to the DigitalTwinService and
     * returns the predicted readiness result as JSON. The simulation is only a
     * preview and does not save any changes to the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function simulate(Request $request)
    {
        $user = $request->user();

        $result = $this->digitalTwinService->simulateSkillImpact(
            $user,
            $request->skill_name
        );

        return response()->json($result);
    }
}
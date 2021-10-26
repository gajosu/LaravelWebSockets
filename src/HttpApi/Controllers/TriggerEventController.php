<?php

namespace Gajosu\LaravelWebSockets\HttpApi\Controllers;

use Gajosu\LaravelWebSockets\Dashboard\DashboardLogger;
use Gajosu\LaravelWebSockets\Facades\StatisticsLogger;
use Illuminate\Http\Request;

class TriggerEventController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->ensureValidSignature($request);

        foreach ($request->json()->get('channels', []) as $channelName) {
            $channel = $this->channelManager->find($request->appId, $channelName);

            optional($channel)->broadcastToEveryoneExcept([
                'channel' => $channelName,
                'event' => $request->json()->get('name'),
                'data' => $request->json()->get('data'),
            ], $request->json()->get('socket_id'));

            DashboardLogger::apiMessage(
                $request->appId,
                $channelName,
                $request->json()->get('name'),
                $request->json()->get('data')
            );

            StatisticsLogger::apiMessage($request->appId);
        }

        return (object) [];
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Services\NutritionalValue;


class LineBotController extends Controller
{

    public function foods (Request $request)
    {
        Log::debug($request->header());
        Log::debug($request->input());

        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN'));
        $lineBot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        $signature = $request->header('x-line-signature');
        if (!$lineBot->validateSignature($request->getContent(), $signature)) {
            abort(400, '署名が無効です。');
        }

        $events = $lineBot->parseEventRequest($request->getContent(), $signature);

        Log::debug($events);

        foreach ($events as $event) {
            if (!($event instanceof TextMessage)) {
                Log::debug('テキストメッセージ形式ではないものが着ました。');
                continue;
            }

            $nutritionalValue = new NutritionalValue();
            $nutritionalValueResponse = $nutritionalValue->searchFoods($event->getText());
            
            Log::debug('$event->getText()の中身:');
            Log::debug($event->getText());

            if (array_key_exists('error', $nutritionalValueResponse)) {
                $replyText = $nutritionalValueResponse['error'][0]['message'];
                $replyToken = $event->getReplyToken();
                $lineBot->replyText($replyToken, $replyText);
                continue;
            }

            Log::debug('$nutritionalValueResponseの中身:');
            Log::debug($nutritionalValueResponse);

            $replyText = '';
            $replyText .= 
                '[食品名]' . "\n" . 
                $nutritionalValueResponse['食品名'] . "\n" . 
                "\n" . 
                "\n" . 
                '[エネルギー（kcal）]' . "\n" . 
                $nutritionalValueResponse['エネルギー（kcal）'] . "\n" . 
                '[飽和脂肪酸]' . "\n" . 
                $nutritionalValueResponse['飽和脂肪酸'] . "\n" . 
                '[たんぱく質]' . "\n" . 
                $nutritionalValueResponse['たんぱく質'] . "\n" . 
                '[一価不飽和脂肪酸]' . "\n" . 
                $nutritionalValueResponse['一価不飽和脂肪酸'] . "\n" . 
                '[カルシウム]' . "\n" . 
                $nutritionalValueResponse['カルシウム'] . "\n" . 
                '[ビタミンK]' . "\n" . 
                $nutritionalValueResponse['ビタミンK'] . "\n";
            // foreach ($nutritionalValueResponse as $food) {
            //     $replyText .= 
            //         $food['食品名'] . "\n" . 
            //         $food['エネルギー（kcal）'] . "\n" . 
            //         $food['飽和脂肪酸'] . "\n" . 
            //         "\n";
            // }

            $replyToken = $event->getReplyToken();
            $lineBot->replyText($replyToken, $replyText);
        }


    }

    public function parrot (Request $request)
    {
        Log::debug($request->header());
        Log::debug($request->input());

        $httpClient = new CurlHTTPClient(env('LINE_ACCESS_TOKEN'));
        $lineBot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        $signature = $request->header('x-line-signature');
        if (!$lineBot->validateSignature($request->getContent(), $signature)) {
            abort(400, '署名が無効です。');
        }

        $events = $lineBot->parseEventRequest($request->getContent(), $signature);

        Log::debug($events);

        foreach ($events as $event) {
            if (!($event instanceof TextMessage)) {
                Log::debug('テキストメッセージ形式ではないものが着ました。');
                continue;
            }

            $replyToken = $event->getReplyToken();
            $replyText = $event->getText();
            $lineBot->replyText($replyToken, $replyText);
        }
    }
}

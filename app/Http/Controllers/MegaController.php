<?php

namespace App\Http\Controllers;

use App\Models\faq;
use App\Models\User;
use App\Models\review;
use App\Models\history;
use App\Models\pricing;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(title="MegaTongue Api Documentation", version="1.0.0")
 */

class MegaController extends Controller
{
    public function apikey(Request $request)
    {
        $user =  User::find(Auth::user()->id);
        $apikey = Str::random(40);
        if ($user) {
            $user->api_key = $apikey;
            $user->update();

            return response()->json([
                'statusCode' => true,
                'message' => 'Apikey has been created successfully',
            ]);
        };
    }

    public function pricing(Request $request)
    {
        $request->validate([
            "name" => "required",
            "amount" => "required",
            "description" => "required",
        ]);

        $price = new pricing;
        $price->name = $request->name;
        $price->amount = $request->amount;
        $price->description = $request->description;
        $price->mode = $request->mode;
        $price->save();

        return response()->json([
            "statusCode" => 200,
            "message" => "Price has been updated successfully",
        ]);
    }

    /**
     * @OA\Post(
     *     path="/translator",
     *     summary="Translate Text",
     *     description="Translate text from one language to another.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="q", type="string", description="Text to be translated."),
     *             @OA\Property(property="source", type="string", description="Source language code."),
     *             @OA\Property(property="target", type="string", description="Target language code."),
     *             @OA\Property(property="format", type="string", description="Translation format.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful translation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Translation successful"),
     *             @OA\Property(property="translated_text", type="string", example="Translated text goes here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error in translation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Translation error")
     *         )
     *     )
     * )
     */

    public function translator(Request $request)
    {
        // Get the API key from the request header or authorization bearer token
        $apiKey = $request->header('apikey'); // Adjust the header name as needed

        if (empty($apiKey)) {
            return response()->json([
                "status code" => 422,
                "message" => "Please provide your API key in the header or as a bearer token."
            ]);
        }

        // Verify the API key against the keys stored in the users' table
        $user = User::where('api_key', $apiKey)->first();

        if (!$user) {
            return response()->json([
                "status code" => 401,
                "message" => "Invalid API key."
            ]);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'q' => 'required',
            'source' => 'required',
            'target' => 'required',
            'format' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status code" => 422,
                "message" => "Validation failed",
                "errors" => $validator->errors(),
            ]);
        }

        $data = array(
            "q" => $request->q,
            "source" => $request->source,
            "target" => $request->target,
            "format" => $request->format
        );

        $json_data = json_encode($data);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://translator.cheapmailing.com.ng/translate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json_data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: session=edb08a19-057b-46e5-bd9e-00346901cf2e'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $decoded_response = json_decode($response, true);

        // Check if the decoded_response contains the 'translatedText' key
        if (isset($decoded_response['translatedText'])) {
            $translated_text = $decoded_response['translatedText'];
        } else {
            $translated_text = 'Translation not available.';
        }

        $history = new history;
        $history->text = $request->q;
        $history->source_language = $request->source;
        $history->destination_language = $request->target;
        $history->format = $request->format;
        $history->response = $translated_text;

        $user->history()->save($history);

        if ($user->history()->save($history)) {
            return response()->json([
                "status code" => 200,
                "message" => $translated_text
            ]);
        } else {
            return response()->json([
                "status code" => 422,
                "message" => "Error",
            ]);
        }
    }


    public function validateArrayData($data)
    {
        foreach ($data as $key => $values) {
            foreach ($values as $value) {
                if (is_null($value) || $value == '') {
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }

    /**
     * @OA\Post(
     *     path="/api/translatefile",
     *     summary="Translate and save CSV file content",
     *     tags={"Translation"},
     *     @OA\RequestBody(
     *         description="CSV File to Translate",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="csvfile",
     *                     description="CSV file to translate",
     *                     type="file",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="CSV file translated and saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="CSV file translated and saved."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="statusCode", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Error message if applicable"),
     *         ),
     *     ),
     * )
     */

    public function translatefile(Request $request)
    {
        // Get the API key from the request header or authorization bearer token
        $apiKey = $request->header('apikey'); // Adjust the header name as needed

        if (empty($apiKey)) {
            return response()->json([
                "status code" => 422,
                "message" => "Please provide your API key in the header or as a bearer token."
            ]);
        }

        // Verify the API key against the keys stored in the users' table
        $user = User::where('api_key', $apiKey)->first();

        if (!$user) {
            return response()->json([
                "status code" => 401,
                "message" => "Invalid API key."
            ]);
        }

        $request->validate([
            'csvfile' => 'required',
        ]);

        if ($request->hasfile('csvfile')) {
            $csv = file_get_contents($request->csvfile);
            $array = array_map('str_getcsv', explode(PHP_EOL, $csv));
            $validate = $this->validateArrayData($array);

            $errorMessages = [];

            // Save the data to the database
            $successMessages = [];

            foreach (array_slice($validate, 1) as $values) {
                $fieldErrors = [];

                if (empty($values[0])) {
                    $fieldErrors[] = "The value in the 'q' field is empty.";
                }

                if (empty($values[1])) {
                    $fieldErrors[] = "The value in the 'source' field is empty.";
                }

                if (empty($values[2])) {
                    $fieldErrors[] = "The value in the 'target' field is empty.";
                }

                if (empty($values[3])) {
                    $fieldErrors[] = "The value in the 'format' field is empty.";
                }

                if (!empty($fieldErrors)) {
                    // If there are errors for this record, collect them.
                    $errorMessages[] = [
                        'row' => $values,
                        'errors' => $fieldErrors,
                    ];
                } else {
                    $filedata = [
                        "q" => $values[0],
                        "source" => $values[1],
                        "target" => $values[2],
                        "format" => $values[3]
                    ];

                    $json_data = json_encode($filedata);

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'http://translator.cheapmailing.com.ng/translate',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $json_data,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Cookie: session=edb08a19-057b-46e5-bd9e-00346901cf2e'
                        ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $decoded_response = json_decode($response, true);

                    // Check if the decoded_response contains the 'translatedText' key
                    if (isset($decoded_response['translatedText'])) {
                        $translated_text = $decoded_response['translatedText'];
                    } else {
                        $translated_text = 'Translation not available.';
                    }

                    $data = new history;
                    $data->text = $values[0];
                    $data->source_language = $values[1];
                    $data->destination_language = $values[2];
                    $data->format = $values[3];
                    $data->response = $translated_text;

                    $user->history()->save($data);

                    $successMessages[] = $translated_text;
                }
            }

            if (!empty($errorMessages)) {
                return response()->json([
                    "status code" => 422,
                    "error_messages" => $errorMessages,
                ]);
            }

            return response()->json([
                "status code" => 200,
                "success_messages" => $successMessages,
            ]);
        }
    }



    //for reviews

    public function addreview(Request $request)
    {
        $add = review::create([
            "user_id" => Auth::user()->id,
            'review' => $request->review,
        ]);

        if ($add) {
            return response()->json([
                "status" => true,
                "message" => "Added review",
            ], 200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "error",
            ], 422);
        }
    }

    public function getreviews(Request $request)
    {
        $getreview = review::all();
        if ($getreview) {
            return response()->json([
                "status" => true,
                "message" =>  $getreview,
            ], 200);
        } else {
            return response()->json([
                "status" => 200,
                "message" => "No Review yet!",
            ], 200);
        }
    }
    /**
     * @OA\Get(
     *     path="/getapiusage",
     *     summary="Get API Usage History",
     *     description="Retrieve the history of API usage (entire data in the history table).",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="API usage history retrieved successfully")
     *         )
     *     )
     * )
     */

    //for api 
    public function getapiusage(Request $request)
    {
        $userId = Auth::user()->id;
        $apiusage = history::where('user_id', $userId)->get();

        if ($apiusage->count() > 0) {
            return response()->json([
                "status" => true,
                "message" => $apiusage->count(),
                "date" => $apiusage->first()->created_at,
            ], 200);
        } else {
            return response()->json([
                "status" => true,
                "message" => "You have not made any request in the last month!",
            ], 200);
        }
    }


    public function getapikey()
    {
        $userkey = User::find(Auth::user()->id)->get();
        foreach ($userkey as $apikey) {
        }
        if ($apikey) {
            return response()->json([
                "status" => true,
                "message" =>  $apikey->api_key,
            ], 200);
        } else {
            return response()->json([
                "status" => true,
                "message" => "You do not have Api Access key, You can request for it!",
            ], 200);
        }
    }

    public function getfaq()
    {
        $faqs = faq::all();

        if ($faqs) {
            return response()->json([
                "status" => true,
                "message" =>  $faqs,
            ], 200);
        } else {
            return response()->json([
                "status" => 200,
                "message" => "No Faq yet!",
            ], 200);
        }
    }
}

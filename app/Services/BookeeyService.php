<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class BookeeyService
{
    protected $merchantId;
    protected $secretKey;
    protected $subMerchantId;
    protected $paymentUrl;
    protected $statusUrl;
    protected $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('bookeey.merchant_id');
        $this->secretKey = config('bookeey.secret_key');
        $this->subMerchantId = config('bookeey.sub_merchant_id');
        $this->paymentUrl = config('bookeey.payment_url');
        $this->statusUrl = config('bookeey.status_url');
        $this->baseUrl = config('bookeey.base_url');

        // Validate required configuration
        if (empty($this->merchantId) || empty($this->secretKey) || empty($this->paymentUrl) || empty($this->statusUrl)) {
            throw new \Exception('Bookeey configuration is incomplete. Please check your .env file.');
        }
    }

    /**
     * Create a payment invoice/request using Bookeey API format.
     *
     * @param array $data
     * @return array
     */
    public function createInvoice($data)
    {
        try {
            $success_url = $data['CallBackUrl'] ?? URL::to('payment_success');
            $fail_url = $data['ErrorUrl'] ?? URL::to('payment_fail');
            $PayerName = $data['CustomerName'] ?? '';
            $PayerPhone = $data['CustomerMobile'] ?? '';

            // If phone is empty, try to get from site settings
            if ($PayerPhone == '') {
                $site = \App\Models\Setting::getSettingModel();
                $PayerPhone = $site?->phone ?? '';
            }

            $amt = number_format((float)($data['InvoiceValue'] ?? 0), 3, '.', '');
            $tex = mt_rand(1000000000000000, 9999999999999999);
            $txnRefNo = $tex;
            $orderId = $data['CustomerReference'] ?? '';
            $rndnum = rand(10000, 99999);
            $crossCat = "GEN";
            $paymentoptions = $data['pay_type'] ?? 'knet';

            // Generate hash
            $dataa = "{$this->merchantId}|{$txnRefNo}|{$success_url}|{$fail_url}|{$amt}|{$crossCat}|{$this->secretKey}|{$rndnum}";
            $hashed = hash('sha512', $dataa);

            // Transaction Details
            $txnDtl = $data['transactionDetails'] ?? [
                [
                    "SubMerchUID" => $this->subMerchantId,
                    "Txn_AMT" => $amt
                ]
            ];

            // Transaction Header
            $txnHdr = [
                "PayFor" => "ECom",
                "Txn_HDR" => $rndnum,
                "PayMethod" => $paymentoptions,
                "BKY_Txn_UID" => "",
                "Merch_Txn_UID" => $orderId,
                "hashMac" => $hashed
            ];

            // App Info
            $appInfo = [
                "APPTyp" => "WEB",
                "OS" => 'Unknown Browser',
                "DevcType" => 'WEB',
                "IPAddrs" => request()->ip(),
                "Country" => "Kuwait",
                "AppVer" => "2.0.0",
                "UsrSessID" => session_id(),
                "APIVer" => "2.0.0"
            ];

            // Payer Details
            $pyrDtl = [
                "Pyr_MPhone" => $PayerPhone,
                "Pyr_Name" => $PayerName
            ];

            // Merchant Details
            $merchDtl = [
                "BKY_PRDENUM" => "ECom",
                "FURL" => $fail_url,
                "MerchUID" => $this->merchantId,
                "SURL" => $success_url
            ];

            // More Details
            $moreDtl = [
                "Cust_Data1" => $data['lang'] ?? app()->getLocale() ?? 'en'
            ];

            // Build POST parameters
            $postParams = [
                'Do_TxnDtl' => $txnDtl,
                'Do_TxnHdr' => $txnHdr,
                'Do_Appinfo' => $appInfo,
                'Do_PyrDtl' => $pyrDtl,
                'Do_MerchDtl' => $merchDtl,
                'DBRqst' => "PY_ECom",
                'Do_MoreDtl' => $moreDtl
            ];

            Log::info('Bookeey Payment Request', [
                'order_id' => $orderId,
                'amount' => $amt,
                'payment_method' => $paymentoptions,
                'pay_type_from_data' => $data['pay_type'] ?? 'not_set',
                'paymentoptions_final' => $paymentoptions
            ]);

            // Send request using cURL
            $ch = curl_init();
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
            ];

            curl_setopt($ch, CURLOPT_URL, $this->paymentUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postParams));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $serverOutput = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('Bookeey Payment cURL Error', ['error' => $curlError]);
                throw new \Exception('Payment gateway connection error: ' . $curlError);
            }

            $decodeOutput = json_decode($serverOutput, true);

            Log::info('Bookeey Payment Response', [
                'http_code' => $httpCode,
                'response' => $decodeOutput
            ]);

            if (isset($decodeOutput['PayUrl'])) {
                return [
                    'success' => true,
                    'invoiceId' => $decodeOutput['PaymentId'] ?? $decodeOutput['TrackId'] ?? null,
                    'invoiceURL' => $decodeOutput['PayUrl'],
                    'trackId' => $decodeOutput['TrackId'] ?? null,
                    'paymentId' => $decodeOutput['PaymentId'] ?? null,
                ];
            } else {
                $errorMessage = $decodeOutput['Message'] ?? $decodeOutput['error'] ?? 'Failed to initiate payment with Bookeey';
                Log::error('Bookeey Payment Response Error', [
                    'response' => $decodeOutput
                ]);
                throw new \Exception($errorMessage);
            }

        } catch (\Exception $e) {
            Log::error('Bookeey Payment Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check payment status using Bookeey API format.
     *
     * @param string $orderId Order ID
     * @return array
     */
    public function getPaymentStatus($orderId)
    {
        try {
            $rndnum = rand(10000, 99999);

            // Generate hash
            $data = "{$this->merchantId}|{$this->secretKey}|{$rndnum}";
            $hashed = hash('sha512', $data);

            $postParams = [
                'Mid' => $this->merchantId,
                'MerchantTxnRefNo' => [$orderId],
                'HashMac' => $hashed
            ];

            Log::info('Bookeey Payment Status Check', [
                'order_id' => $orderId,
                'merchant_id' => $this->merchantId
            ]);

            // Send request using cURL
            $ch = curl_init();
            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
            ];

            curl_setopt($ch, CURLOPT_URL, $this->statusUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postParams));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $serverOutput = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('Bookeey Status cURL Error', ['error' => $curlError]);
                throw new \Exception('Status check connection error: ' . $curlError);
            }

            $responseData = json_decode($serverOutput, true);

            Log::info('Bookeey Status Response', [
                'http_code' => $httpCode,
                'response' => $responseData
            ]);

            if ($httpCode == 200 && isset($responseData['PaymentStatus'])) {
                $paymentStatus = $responseData['PaymentStatus'];

                // Handle array of payment statuses
                if (is_array($paymentStatus) && isset($paymentStatus[0])) {
                    $statusData = $paymentStatus[0];
                    $finalStatus = strtoupper(trim($statusData['finalStatus'] ?? ''));
                    $status = strtoupper(trim($statusData['Status'] ?? ''));
                    $statusDescription = strtoupper(trim($statusData['StatusDescription'] ?? ''));
                    $errorCode = trim($statusData['ErrorCode'] ?? '');
                    
                    // Check multiple fields to determine if payment is successful
                    // From logs: finalStatus="success", StatusDescription="Transaction Success", ErrorCode="0"
                    $isPaid = (
                        $finalStatus === 'SUCCESS' || 
                        $status === 'SUCCESS' || 
                        $status === 'PAID' || 
                        $status === 'CAPTURED' ||
                        (strpos($statusDescription, 'SUCCESS') !== false) || // "TRANSACTION SUCCESS" contains "SUCCESS"
                        ($errorCode === '0' && ($finalStatus === 'SUCCESS' || strpos($statusDescription, 'SUCCESS') !== false)) ||
                        ($errorCode === '0' && $finalStatus === 'SUCCESS') // ErrorCode="0" + finalStatus="success" (converted to "SUCCESS")
                    );
                    
                    $actualStatus = $finalStatus ?: $status ?: $statusDescription;
                    
                    // Log for debugging
                    Log::info('Bookeey Payment Status Check Result', [
                        'finalStatus' => $finalStatus,
                        'status' => $status,
                        'statusDescription' => $statusDescription,
                        'errorCode' => $errorCode,
                        'isPaid' => $isPaid,
                        'actualStatus' => $actualStatus
                    ]);

                    return [
                        'success' => true,
                        'status' => $isPaid ? 'Paid' : ($actualStatus ?: 'Pending'),
                        'data' => $responseData,
                        'isPaid' => $isPaid,
                        'paymentStatus' => $statusData
                    ];
                }
            }

            $errorMessage = $responseData['Message'] ?? $responseData['error'] ?? 'Status check failed';
            Log::error('Bookeey Status Error', [
                'response' => $responseData
            ]);

            return [
                'success' => false,
                'status' => 'Failed',
                'error' => $errorMessage,
                'data' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('Bookeey Status Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'status' => 'Failed',
                'error' => $e->getMessage()
            ];
        }
    }
}

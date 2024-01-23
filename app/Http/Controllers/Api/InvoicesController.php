<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Stripe\PlansManager;
use Illuminate\Http\Request;
use Auth;
use Exception;
use App\Models\User;

class InvoicesController extends Controller
{
    public function listInvoices()
    {
        $invoices = auth()->user()->invoices();
        return response()->json([
            'success' => true,
            'invoices' => $invoices
        ]);
    }

    public function getInvoice(Request $request, $invoice_id)
    {
        $user = auth()->user();
        $invoice = $user->findInvoiceOrFail($invoice_id);

        try {
            $plan = end($invoice->lines->data)->plan->id;
        } catch (Exception $e) {
            $plan = ucfirst($user->currentSubscription()->stripe_price);
        }

        $plan = app()->make(PlansManager::class)->parsePlanName($plan);
        $plan = ucwords(str_replace(['-', '_'], ' ', $plan));
        $fileName = "test.pdf";
        $headers = ['Content-Type: application/pdf'];
        $fileSource =  $invoice->download([
            'vendor' => 'Merch Informer LLC',
            'product' => $plan . ' Subscription',
            'vendor_address' => [
                'street' => '7993 Grasmere Drive',
                'state' => 'CO',
                'zip' => '80301'
            ]
        ]);

        return $user->downloadInvoice($invoice_id, [
            'vendor' => 'Merch Informer LLC',
            'product' => $plan . ' Subscription',
            'vendor_address' => [
                'street' => '7993 Grasmere Drive',
                'state' => 'CO',
                'zip' => '80301'
            ]
        ]);


        // return response()->download(
        //     $fileSource,
        //     $fileName,
        //     $headers
        // );
    }
}

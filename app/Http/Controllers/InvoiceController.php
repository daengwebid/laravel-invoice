<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\Invoice;
use App\Product;
use App\Invoice_detail;
use PDF;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoice  = Invoice::with(['customer', 'detail'])->orderBy('created_at', 'DESC')->paginate(10);
        return view('invoice.index', compact('invoice'));
    }

    public function create()
    {
        $customers = Customer::orderBy('created_at', 'DESC')->get();
        return view('invoice.create', compact('customers'));
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'customer_id' => 'required|exists:customers,id'
        ]);

        try {
            $invoice = Invoice::create([
                'customer_id' => $request->customer_id,
                'total' => 0
            ]);

            return redirect(route('invoice.edit', ['id' => $invoice->id]));
        } catch(\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $invoice = Invoice::with(['customer', 'detail', 'detail.product'])->find($id);
        $products = Product::orderBy('title', 'ASC')->get();
        return view('invoice.edit', compact('invoice', 'products'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer'
        ]);

        try {
            $invoice = Invoice::find($id);
            $product = Product::find($request->product_id);

            $invoice_detail = $invoice->detail()->where('product_id', $product->id)->first();
            if ($invoice_detail) {
                $invoice_detail->update([
                    'qty' => $invoice_detail->qty + $request->qty
                ]);
            } else {
                Invoice_detail::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $request->product_id,
                    'price' => $product->price,
                    'qty' => $request->qty
                ]);
            }
            
            return redirect()->back()->with(['success' => 'Product Telah Ditambahkan']);
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }

    public function deleteProduct($id)
    {
        $detail = Invoice_detail::find($id);
        $detail->delete();
        return redirect()->back()->with(['success' => 'Product telah dihapus']);
    }

    public function destroy($id)
    {
        $invoice = Invoice::find($id);
        $invoice->delete();
        return redirect()->back()->with(['success' => 'Data telah dihapus']);
    }

    public function generateInvoice($id)
    {
        $invoice = Invoice::with(['customer', 'detail', 'detail.product'])->find($id);
        $pdf = PDF::loadView('invoice.print', compact('invoice'))->setPaper('a4', 'landscape');
        return $pdf->stream();
    }
}

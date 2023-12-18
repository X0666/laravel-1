<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        try {
            $id = $request->input('id');
            $limit = $request->input('limit', 20);
            $status = $request->input('status');
            $page = $request->input('page', 1);

            if ($id) {
                $transaction = Transaction::with(['items'])->find($id);

                if ($transaction) {
                    return ResponseFormatter::success(
                        $transaction,
                        'Data transaksi berhasil'
                    );
                } else {
                    return ResponseFormatter::error(
                        null,
                        'Data transaksi kosong',
                        404
                    );
                }
            }

            $transaction = Transaction::with(['items'])->where('users_id', Auth::user()->id);

            if ($status) {
                $transaction->where('status', $status);
            }

            $transactions = $transaction->paginate($limit, ['*'], 'page', $page);

            return ResponseFormatter::success(
                $transactions,
                'Data list berhasil di ambil'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Terjadi kesalahan saat mengambil data transaksi',
                500
            );
        }
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'exists:products,id',
            'total_price' => 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING,PAID,SUCCESS,CANCELLED,FAILED,SHIPPING,SHIPPED'
        ]);

        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $request->total_price,
            'shipping_price' => $request->shipping_price,
            'status' => $request->status,
        ]);

        foreach ($request->items as $product) {
            TransactionItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product['id'],
                'transactions_id' => $transaction->id,
                'quantity' => $product['quantity']
            ]);
        }

        return ResponseFormatter::success($transaction->load('items.product'), 'Transaksi berhasil');
    }

    public function paid(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'payment_id' => 'required'
            ]);

            $paymentMethod = PaymentMethod::find($request->payment_id);

            if (!$paymentMethod) {
                return ResponseFormatter::error(
                    null,
                    'Metode payment tidak ditemukan',
                    404
                );
            }

            $transaction = Transaction::with(['items.product'])->find($request->id);

            if (!$transaction) {
                return ResponseFormatter::error(
                    null,
                    'Transaksi tidak ditemukan',
                    404
                );
            }

            $transaction->update([
                'payment' => $paymentMethod->name,
                'status' => "PAID"
            ]);

            return ResponseFormatter::success(
                $transaction,
                'Transaksi berhasil dibayar'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Terjadi kesalahan saat membayar transaksi',
                500
            );
        }
    }

    public function paymentMehtod(){
        try {
            $paymentMethod = PaymentMethod::all();

            if (!$paymentMethod) {
                return ResponseFormatter::error(
                    null,
                    'Data metode payment tidak ditemukan',
                    404
                );
            }

            return ResponseFormatter::success(
                $paymentMethod,
                'Data metode payment berhasil di ambil'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Terjadi kesalahan saat mengambil data metode payment',
                500
            );
        }
    }

    public function allForAdmin(Request $request)
    {
        try {
            $id = $request->input('id');
            $limit = $request->input('limit', 20);
            $status = $request->input('status');
            $page = $request->input('page', 1);

            if ($id) {
                $transaction = Transaction::with(['items.product'])->find($id);

                if ($transaction) {
                    return ResponseFormatter::success(
                        $transaction,
                        'Data transaksi berhasil'
                    );
                } else {
                    return ResponseFormatter::error(
                        null,
                        'Data transaksi kosong',
                        404
                    );
                }
            }

            $transaction = Transaction::with(['items.product']);

            if ($status) {
                $transaction->where('status', $status);
            }

            $transactions = $transaction->orderBy('updated_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return ResponseFormatter::success(
                $transactions,
                'Data list berhasil di ambil'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Terjadi kesalahan saat mengambil data transaksi',
                500
            );
        }
    }
}

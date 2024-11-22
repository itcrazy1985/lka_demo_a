<?php

namespace App\Http\Controllers;

use App\Enums\TransactionName;
use App\Enums\UserType;
use App\Models\Admin\UserLog;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->roles->pluck('title');

        $master_count = User::where('type', UserType::Master)->when($role[0] = 'Admin', function ($query) use ($user) {
            $query->where('agent_id', $user->id);
        })->count();

        $agent_count = User::where('type', UserType::Agent)
            ->when($role[0] === 'Master', function ($query) use ($user) {
                return $query->where('agent_id', $user->id);
            })
            ->count();

        $player_count = User::where('type', UserType::Player)
            ->when($role[0] === 'Agent', function ($query) use ($user) {
                return $query->where('agent_id', $user->id);
            })
            ->count();

        $totalDeposit = $this->getTotalDeposit();
        $totalWithdraw = $this->getTotalWithdraw();

        return view('admin.dashboard', compact(
            'agent_count',
            'player_count',
            'master_count',
            'user',
            'totalDeposit',
            'totalWithdraw'
        ));
    }

    public function balanceUp(Request $request)
    {
        abort_if(
            Gate::denies('admin_access'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden |You cannot Access this page because you do not have permission'
        );

        $request->validate([
            'balance' => 'required|numeric',
        ]);

        // Get the current user (admin)
        $admin = Auth::user();

        // Get the current balance before the update
        $openingBalance = $admin->wallet->balanceFloat;

        // Update the balance using the WalletService
        app(WalletService::class)->deposit($admin, $request->balance, TransactionName::CapitalDeposit);

        // Record the transaction in the transactions table
        Transaction::create([
            'payable_type' => get_class($admin),
            'payable_id' => $admin->id,
            'wallet_id' => $admin->wallet->id,
            'type' => 'deposit',
            'amount' => $request->balance,
            'confirmed' => true,
            'meta' => json_encode([
                'name' => TransactionName::CapitalDeposit,
                'opening_balance' => $openingBalance,
                'new_balance' => $admin->wallet->balanceFloat,
                'target_user_id' => $admin->id,
            ]),
            'uuid' => Str::uuid()->toString(),
        ]);

        return back()->with('success', 'Add New Balance Successfully.');
    }

    public function logs($id)
    {
        $logs = UserLog::with('user')->where('user_id', $id)->get();

        return view('admin.logs', compact('logs'));
    }

    private function getTotalWithdraw()
    {
        return Auth::user()->transactions()->with('targetUser')->select(
            DB::raw('SUM(transactions.amount)/100 as amount')
        )
            ->whereIn('transactions.name', ['debit_transfer', 'credit_transfer'])
            ->where('transactions.type', 'withdraw')
            ->first();
    }

    private function getTotalDeposit()
    {
        return Auth::user()->transactions()->with('targetUser')
            ->select(DB::raw('SUM(transactions.amount)/100 as amount'))
            ->whereIn('transactions.name', ['debit_transfer', 'credit_transfer'])
            ->where('transactions.type', 'deposit')
            ->first();
    }
}

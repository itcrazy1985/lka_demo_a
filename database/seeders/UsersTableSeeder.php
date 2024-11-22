<?php

namespace Database\Seeders;

use App\Enums\TransactionName;
use App\Enums\TransactionType;
use App\Enums\UserType;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->createUser(UserType::Admin, 'Owner', 'linkhant', '09123456789');
        (new WalletService)->deposit($admin, 10 * 100_000, TransactionName::CapitalDeposit);

        $master = $this->createUser(UserType::Master, 'Master', 'LM898737', '0935234545', $admin->id);
        (new WalletService)->transfer($admin, $master, 5 * 100_000, TransactionName::CreditTransfer);

        $agent_1 = $this->createUser(UserType::Agent, 'Agent 1', 'L898737', '09112345674', $master->id, 'vH4HueE9');
        (new WalletService)->transfer($master, $agent_1, 5 * 100_00, TransactionName::CreditTransfer);

        $player_1 = $this->createUser(UserType::Player, 'Player 1', 'L111111', '09111111111', $agent_1->id);
        (new WalletService)->transfer($agent_1, $player_1, 30000, TransactionName::CreditTransfer);
    }

    private function createUser(UserType $type, $name, $user_name, $phone, $parent_id = null, $referral_code = null)
    {
        return User::create([
            'name' => $name,
            'user_name' => $user_name,
            'phone' => $phone,
            'password' => Hash::make('password'),
            'agent_id' => $parent_id,
            'status' => 1,
            'referral_code' => $referral_code,
            'is_changed_password' => 1,
            'type' => $type->value,
        ]);
    }
}

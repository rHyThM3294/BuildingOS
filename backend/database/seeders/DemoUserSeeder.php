<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 專門給登入 demo 用的固定帳號，用 firstOrCreate 寫成冪等的，可以放進
 * 部署流程每次開機都跑一次也不會重複建立或報錯（跟 DemoDataSeeder
 * 那種一次性、用 create() 硬塞展示資料的邏輯不一樣）。
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'demo@buildingos.test'],
            ['name' => 'Demo 使用者', 'password' => Hash::make('buildingos-demo')],
        );
    }
}

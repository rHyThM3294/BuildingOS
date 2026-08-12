<?php

namespace Database\Seeders;

use App\Models\PackageItem;
use App\Models\ParkingLog;
use App\Models\VisitorLog;
use Illuminate\Database\Seeder;

/**
 * 面試 Demo 用的展示資料：車輛門禁、包裹、訪客三個模組各塞幾筆
 * 有變化的紀錄，讓 Swagger / 前端頁面不會是空的。
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $parkingLogs = [
            ['plate_number' => 'ABC-1234', 'direction' => 'in', 'status' => 'success', 'owner_name' => '陳志明', 'recognized_at' => $now->copy()->subHours(5)],
            ['plate_number' => 'ABC-1234', 'direction' => 'out', 'status' => 'success', 'owner_name' => '陳志明', 'recognized_at' => $now->copy()->subHours(3)],
            ['plate_number' => 'BWK-5678', 'direction' => 'in', 'status' => 'success', 'owner_name' => '林淑芬', 'recognized_at' => $now->copy()->subHours(2)],
            ['plate_number' => 'XYZ-9999', 'direction' => 'in', 'status' => 'failed', 'owner_name' => null, 'recognized_at' => $now->copy()->subHour()],
            ['plate_number' => 'RMK-2468', 'direction' => 'in', 'status' => 'success', 'owner_name' => '王建宏', 'recognized_at' => $now->copy()->subMinutes(40)],
            ['plate_number' => 'RMK-2468', 'direction' => 'out', 'status' => 'success', 'owner_name' => '王建宏', 'recognized_at' => $now->copy()->subMinutes(10)],
        ];
        foreach ($parkingLogs as $log) {
            ParkingLog::create($log);
        }

        $packages = [
            ['tracking_no' => 'SF1234567890', 'recipient_unit' => 'A-1203', 'recipient_name' => '陳志明', 'courier' => '黑貓宅急便', 'status' => 'pending', 'arrived_at' => $now->copy()->subHours(6), 'collected_at' => null],
            ['tracking_no' => 'TW9988776655', 'recipient_unit' => 'B-0805', 'recipient_name' => '林淑芬', 'courier' => '7-ELEVEN 交貨便', 'status' => 'notified', 'arrived_at' => $now->copy()->subHours(4), 'collected_at' => null],
            ['tracking_no' => 'SHP20260810001', 'recipient_unit' => 'A-1502', 'recipient_name' => '王建宏', 'courier' => '蝦皮店到店', 'status' => 'collected', 'arrived_at' => $now->copy()->subDay(), 'collected_at' => $now->copy()->subHours(20)],
            ['tracking_no' => 'FE5566778899', 'recipient_unit' => 'C-0301', 'recipient_name' => '張雅婷', 'courier' => '全家店到店', 'status' => 'pending', 'arrived_at' => $now->copy()->subHour(), 'collected_at' => null],
            ['tracking_no' => 'PCH1122334455', 'recipient_unit' => 'B-1108', 'recipient_name' => '李冠廷', 'courier' => 'PChome 24h', 'status' => 'notified', 'arrived_at' => $now->copy()->subMinutes(30), 'collected_at' => null],
        ];
        foreach ($packages as $package) {
            PackageItem::create($package);
        }

        $visitors = [
            ['visitor_name' => '王大明', 'visitor_type' => 'guest', 'target_unit' => 'A-1203', 'status' => 'entered', 'registered_at' => $now->copy()->subHours(3), 'notified_at' => $now->copy()->subHours(3)->addMinutes(2)],
            ['visitor_name' => 'foodpanda 外送員', 'visitor_type' => 'delivery', 'target_unit' => 'B-0805', 'status' => 'left', 'registered_at' => $now->copy()->subHours(2), 'notified_at' => $now->copy()->subHours(2)->addMinutes(1)],
            ['visitor_name' => 'Uber Eats 外送員', 'visitor_type' => 'delivery', 'target_unit' => 'C-0301', 'status' => 'notified', 'registered_at' => $now->copy()->subMinutes(25), 'notified_at' => $now->copy()->subMinutes(24)],
            ['visitor_name' => '陳小華', 'visitor_type' => 'guest', 'target_unit' => 'A-1502', 'status' => 'waiting', 'registered_at' => $now->copy()->subMinutes(5), 'notified_at' => null],
        ];
        foreach ($visitors as $visitor) {
            VisitorLog::create($visitor);
        }
    }
}

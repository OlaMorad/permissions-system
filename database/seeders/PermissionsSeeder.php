<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // عامة
            'manage users',
            'assign employee permissions',
            'create user',
            'edit user',
            'delete user',
            'change user password',
            'view activity logs',
            'export reports',
            'archive data',
            'view dashboard',

            // الديوان
            'classify correspondence',
            'view transaction details',
            'forward transaction to departments',
            'add new transaction',
            'import transaction from Word',
            'edit transaction',
            'delete transaction',
            'export transactions to Excel',
            'import transactions from Excel',
            'notify new mail',
            'search transaction',
            'view analytics reports',
            'manage front desk staff',
            'monitor front desk performance',
            'edit front desk permissions',
            'auto archive transactions',

            // المالية
            'review bank receipts',
            'confirm payment',
            'forward finance transaction to front desk',
            'view internal payments',
            'view external payments',
            'export finance records',
            'import finance records',
            'view finance analytics',
            'manage finance staff',
            'monitor finance performance',
            'manage operational budget',
            'manage salaries',
            'export salary reports',
            'edit finance staff permissions',
            'auto archive finance transactions',
            'finance notifications',
            'search finance transaction',

            // المجالس العلمية
            'receive academic requests',
            'view academic request details',
            'enter committee decision',
            'upload session minutes',
            'manage academic sessions schedule',
            'archive academic decisions',
            'manage academic committee staff',
            'monitor academic performance',
            'search academic transaction',
            'notify academic inbox',

            // الشهادات
            'manage certificate transactions',
            'issue certificates',
            'manage doctors registry',
            'manage certificate archive',
            'manage certificate staff',
            'search certificate transaction',
            'notify certificate inbox',

            // الامتحانات
            'manage applicants files',
            'record exam results',
            'assign students to rooms',
            'manage question bank',
            'add exam question',
            'edit exam question',
            'delete exam question',
            'encrypt exam questions',
            'create exam schedule',
            'generate exam statistics',
            'notify exam schedule',
            'manage used questions',
            'face recognition login',
            'exam start notifications',
            'manage exam staff permissions',
            'auto archive exam data',

            // الإقامة
            'receive residency requests',
            'review residency data',
            'accept residency request',
            'reject residency request',
            'archive residency transactions',
            'import old residency records',
            'notify residency inbox',
            'manage residency staff',

            // المفاضلة
            'create new selection round',
            'define selection criteria',
            'review selection application',
            'archive selection transactions',
            'announce selection results',
            'notify selection round',
            'manage selection staff',
            'enter historical selections',

            // المدير العام
            'full access to all departments',
            'send system-wide announcements',
            'approve sensitive changes',
            'manage user complaints',
            'view full system archive',
            'set platform operating hours',

            // نائب المدير
            'review pending requests',
            'edit head of department accounts',
            'view system archive',
            'manage complaints',
            'send internal front desk messages',

            // الطبيب
            'register on platform',
            'upload personal documents',
            'apply for services',
            'track application status',
            'view exam results',
            'download certificates',
            'edit personal information',
            'submit inquiry or complaint',
            'take online exams',
            'auto exam correction',
            'face login',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}

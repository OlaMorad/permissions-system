<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Role_PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Admin: كل الصلاحيات
        $admin = Role::where('name', 'admin')->first();
        $admin->syncPermissions(Permission::all());

        // ✅ Sub Admin: صلاحيات عامة
        $subAdmin = Role::where('name', 'sub_admin')->first();
        $subAdmin->syncPermissions([
            'manage users',
            'assign employee permissions',
            'create user',
            'edit user',
            'delete user',
            'change user password',
            'view dashboard',
            'view activity logs',
            'export reports',
        ]);

        // ✅ Head of Front Desk
        $headFrontDesk = Role::where('name', 'Head of Front Desk')->first();
        $headFrontDesk->syncPermissions([
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
        ]);

        // ✅ Front Desk User
        $frontDesk = Role::where('name', 'Front Desk User')->first();
        $frontDesk->syncPermissions([
            'classify correspondence',
            'view transaction details',
            'add new transaction',
            'search transaction',
            'notify new mail',
        ]);

        // ✅ Head of Finance Officer
        $headFinance = Role::where('name', 'Head of Finance Officer')->first();
        $headFinance->syncPermissions([
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
        ]);

        // ✅ Finance Officer
        $finance = Role::where('name', 'Finance Officer')->first();
        $finance->syncPermissions([
            'review bank receipts',
            'confirm payment',
            'view internal payments',
            'view external payments',
            'search finance transaction',
        ]);

        // ✅ Head of Academic Committee
        $headAcademic = Role::where('name', 'Head of Academic Committee')->first();
        $headAcademic->syncPermissions([
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
        ]);

        // ✅ Academic Committee
        $academic = Role::where('name', 'Academic Committee')->first();
        $academic->syncPermissions([
            'view academic request details',
            'enter committee decision',
            'upload session minutes',
            'search academic transaction',
        ]);

        // ✅ Head of Certificate Officer
        $headCert = Role::where('name', 'Head of Certificate Officer')->first();
        $headCert->syncPermissions([
            'manage certificate transactions',
            'issue certificates',
            'manage doctors registry',
            'manage certificate archive',
            'manage certificate staff',
            'search certificate transaction',
            'notify certificate inbox',
        ]);

        // ✅ Certificate Officer
        $cert = Role::where('name', 'Certificate Officer')->first();
        $cert->syncPermissions([
            'issue certificates',
            'search certificate transaction',
        ]);

        // ✅ Head of Exam Officer
        $headExam = Role::where('name', 'Head of Exam Officer')->first();
        $headExam->syncPermissions([
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
        ]);

        // ✅ Exam Officer
        $exam = Role::where('name', 'Exam Officer')->first();
        $exam->syncPermissions([
            'record exam results',
            'assign students to rooms',
            'face recognition login',
        ]);

        // ✅ Head of Residency Officer
        $headResidency = Role::where('name', 'Head of Residency Officer')->first();
        $headResidency->syncPermissions([
            'receive residency requests',
            'review residency data',
            'accept residency request',
            'reject residency request',
            'archive residency transactions',
            'import old residency records',
            'notify residency inbox',
            'manage residency staff',
        ]);

        // ✅ Residency Officer
        $residency = Role::where('name', 'Residency Officer')->first();
        $residency->syncPermissions([
            'review residency data',
            'accept residency request',
            'reject residency request',
        ]);

        // ✅ Head of Selection & Admission Officer
        $headSelection = Role::where('name', 'Head of Selection & Admission Officer')->first();
        $headSelection->syncPermissions([
            'create new selection round',
            'define selection criteria',
            'review selection application',
            'archive selection transactions',
            'announce selection results',
            'notify selection round',
            'manage selection staff',
            'enter historical selections',
        ]);

        // ✅ Selection & Admission Officer
        $selection = Role::where('name', 'Selection & Admission Officer')->first();
        $selection->syncPermissions([
            'review selection application',
            'archive selection transactions',
        ]);

        // ✅ Doctor
        $doctor = Role::where('name', 'Doctor')->first();
        $doctor->syncPermissions([
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
        ]);
    }
}

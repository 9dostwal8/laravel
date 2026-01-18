<?php

use App\Livewire\Projects\Summary;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/login', function () {
    return redirect(route('filament.admin.auth.login'));
})->name('login');

Route::get('/projects/{project}/summary', Summary::class)->name('project.summary');

Route::get('storage-link', function () {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
});

Route::get('clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

Route::get('migrate-new-tables', function () {

    $appends = [
//        '2024_05_11_084209_add_extra_fields_to_projects.php',
//        '2024_05_11_120129_create_investors_representative_table.php',
//        '2024_05_11_124836_add_ranking_to_amenities.php',
//        '2024_05_11_125044_add_extra_fields_to_project_amenities.php',
//        '2024_05_15_170338_add_project_type_column_to_statuses.php',
//        '2024_04_26_075056_add_company_columns_to_investors_table.php',
//        '2024_06_17_163241_add_extra_fields_in_progress_status_to_projects.php',
//        '2024_08_05_084509_add_extra_fields_2_to_projects_table.php',
//        '2024_08_09_100427_add_can_see_to_users_table.php',
//        '2024_08_25_160330_add_doc_to_progress_table.php',
//        '2024_09_18_081939_add_creator_to_investor_table.php',
//        '2024_09_18_082140_add_organization_id_to_commands_table.php',
//        '2025_04_09_133852_add_activity_area_limit_to_users.php',
//        '2025_05_21_165852_add_show_column_to_projects.php',
//        '2025_08_10_171103_create_project_countries.php',
          '2025_09_18_080910_create_passkeys_table.php',
          '2025_09_18_081105_add_two_factor_authentication_columns.php',
    ];

    try {
        foreach ($appends as $append) {
            \Illuminate\Support\Facades\Artisan::call("migrate --path=/database/migrations/{$append}");
        }
        echo 'Success';
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }

});


Route::get('change-currency', function () {
    $dates = [
        [
            'from' => '2000/01/01',
            'to' => '2020/12/19',
            'rate' => 1182
        ],
        [
            'from' => '2020/12/20',
            'to' => '2023/02/07',
            'rate' => 1450
        ],
        [
            'from' => '2023/02/08',
            'to' => now()->format('Y/m/d'),
            'rate' => 1300
        ],
    ];


    foreach ($dates as $date) {
        $projects = \App\Models\Project::query()
            ->whereBetween('licence_received_at', [$date['from'], $date['to']])
            ->get();
        $rate = $date['rate'];

        foreach ($projects as $project) {
            foreach ($project->projectVariants as $variant) {
                $variant->currency_rate = $rate;
                $check_dollar = ! empty($variant->capital_dollar) || $variant->capital_dollar > 0;
                $check_dinar = ! empty($variant->capital_dinar) || $variant->capital_dinar > 0;

                if ($check_dollar) {
                    $variant->capital_dinar = $variant->capital_dollar * $rate;
                }

                if (!$check_dollar && $check_dinar) {
                    $variant->capital_dollar = $variant->capital_dinar / $rate;
                }

                if ($variant->isDirty()) {
                    $variant->save();
                }
            }

        }

    }

});

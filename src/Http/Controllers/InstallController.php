<?php

namespace Brazidev\Brazidesk\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Brazidev\Brazidesk\Models\Agent;
use Brazidev\Brazidesk\Models\Setting;
use Brazidev\Brazidesk\Seeds\SettingsTableSeeder;
use Brazidev\Brazidesk\Seeds\BrazideskTableSeeder;

class InstallController extends Controller
{
    public $migrations_tables = [];

    public function __construct()
    {
        $migrations = \File::files(dirname(dirname(__FILE__)).'/Migrations');
        foreach ($migrations as $migration) {
            $this->migrations_tables[] = basename($migration, '.php');
        }
    }

    public function publicAssets()
    {
        $public = $this->allFilesList(public_path('vendor/brazidesk'));
        $assets = $this->allFilesList(base_path('vendor/brazidev/brazidesk/src/Public'));
        if ($public !== $assets) {
            Artisan::call('vendor:publish', [
                '--provider' => 'Brazidev\\Brazidesk\\BrazideskServiceProvider',
                '--tag'      => ['public'],
            ]);
        }
    }

    /*
     * Initial install form
     */

    public function index()
    {
        // if all migrations are not yet installed or missing settings table,
        // then start the initial install with admin and master template choices
        if (count($this->migrations_tables) == count($this->inactiveMigrations())
            || in_array('2015_10_08_123457_create_settings_table', $this->inactiveMigrations())
        ) {
            $views_files_list = $this->viewsFilesList('../resources/views/') + ['another' => trans('brazidesk::install.another-file')];
            $inactive_migrations = $this->inactiveMigrations();
            // if Laravel v5.2 or 5.3
            if (version_compare(app()->version(), '5.2.0', '>=')) {
                $users_list = User::pluck('name', 'id')->toArray();
            } else { // if Laravel v5.1
                $users_list = User::lists('name', 'id')->toArray();
            }

            return view('brazidesk::install.index', compact('views_files_list', 'inactive_migrations', 'users_list'));
        }

        // other than that, Upgrade to a new version, installing new migrations and new settings slugs
        if (Agent::isAdmin()) {
            $inactive_migrations = $this->inactiveMigrations();
            $inactive_settings = $this->inactiveSettings();

            return view('brazidesk::install.upgrade', compact('inactive_migrations', 'inactive_settings'));
        }
        \Log::emergency('Brazidesk needs upgrade, admin should login and visit brazidesk-install to activate the upgrade');
        throw new \Exception('Brazidesk needs upgrade, admin should login and visit brazidesk install route');
    }

    /*
     * Do all pre-requested setup
     */

    public function setup(Request $request)
    {
        $master = $request->master;
        if ($master == 'another') {
            $another_file = $request->other_path;
            $views_content = strstr(substr(strstr($another_file, 'views/'), 6), '.blade.php', true);
            $master = str_replace('/', '.', $views_content);
        }
        $this->initialSettings($master);
        $admin_id = $request->admin_id;
        $admin = User::find($admin_id);
        $admin->brazidesk_admin = true;
        $admin->save();

        return redirect('/'.Setting::grab('main_route'));
    }

    /*
     * Do version upgrade
     */

    public function upgrade()
    {
        if (Agent::isAdmin()) {
            $this->initialSettings();

            return redirect('/'.Setting::grab('main_route'));
        }
        \Log::emergency('Brazidesk upgrade path access: Only admin is allowed to upgrade');
        throw new \Exception('Brazidesk upgrade path access: Only admin is allowed to upgrade');
    }

    /*
     * Initial installer to install migrations, seed default settings, and configure the master_template
     */

    public function initialSettings($master = false)
    {
        $inactive_migrations = $this->inactiveMigrations();
        if ($inactive_migrations) { // If a migration is missing, do the migrate
            Artisan::call('vendor:publish', [
                '--provider' => 'Brazidev\\Brazidesk\\BrazideskServiceProvider',
                '--tag'      => ['db'],
            ]);
            Artisan::call('migrate');

            $this->settingsSeeder($master);

            // if this is the first install of the html editor, seed old posts text to the new html column
            if (in_array('2016_01_15_002617_add_htmlcontent_to_brazidesk_and_comments', $inactive_migrations) &&
                !(isset($_SERVER['ARTISAN_TICKETIT_INSTALLING']) && $_SERVER['ARTISAN_TICKETIT_INSTALLING'])) {
                Artisan::call('brazidesk:htmlify');
            }
        } elseif ($this->inactiveSettings()) { // new settings to be installed

            $this->settingsSeeder($master);
        }
        \Cache::forget('settings');
    }

    /**
     * Run the settings table seeder.
     *
     * @param string $master
     */
    public function settingsSeeder($master = false)
    {
        $cli_path = 'config/brazidesk.php'; // if seeder run from cli, use the cli path
        $provider_path = '../config/brazidesk.php'; // if seeder run from provider, use the provider path
        $config_settings = [];
        $settings_file_path = false;
        if (File::isFile($cli_path)) {
            $settings_file_path = $cli_path;
        } elseif (File::isFile($provider_path)) {
            $settings_file_path = $provider_path;
        }
        if ($settings_file_path) {
            $config_settings = include $settings_file_path;
            File::move($settings_file_path, $settings_file_path.'.backup');
        }
        $seeder = new SettingsTableSeeder();
        if ($master) {
            $config_settings['master_template'] = $master;
        }
        $seeder->config = $config_settings;
        $seeder->run();
    }

    /**
     * Get list of all files in the views folder.
     *
     * @return mixed
     */
    public function viewsFilesList($dir_path)
    {
        $dir_files = File::files($dir_path);
        $files = [];
        foreach ($dir_files as $file) {
            $path = basename($file);
            $name = strstr(basename($file), '.', true);
            $files[$name] = $path;
        }

        return $files;
    }

    /**
     * Get list of all files in the views folder.
     *
     * @return mixed
     */
    public function allFilesList($dir_path)
    {
        $files = [];
        if (File::exists($dir_path)) {
            $dir_files = File::allFiles($dir_path);
            foreach ($dir_files as $file) {
                $path = basename($file);
                $name = strstr(basename($file), '.', true);
                $files[$name] = $path;
            }
        }

        return $files;
    }

    /**
     * Get all Brazidesk Package migrations that were not migrated.
     *
     * @return array
     */
    public function inactiveMigrations()
    {
        $inactiveMigrations = [];
        $migration_arr = [];

        // Package Migrations
        $tables = $this->migrations_tables;

        // Application active migrations
        $migrations = DB::select('select * from '.DB::getTablePrefix().'migrations');

        foreach ($migrations as $migration_parent) { // Count active package migrations
            $migration_arr[] = $migration_parent->migration;
        }

        foreach ($tables as $table) {
            if (!in_array($table, $migration_arr)) {
                $inactiveMigrations[] = $table;
            }
        }

        return $inactiveMigrations;
    }

    /**
     * Check if all Brazidesk Package settings that were not installed to setting table.
     *
     * @return bool
     */
    public function inactiveSettings()
    {
        $seeder = new SettingsTableSeeder();

        // Package Settings
        // if Laravel v5.2 or 5.3
        if (version_compare(app()->version(), '5.2.0', '>=')) {
            $installed_settings = DB::table('brazidesk_settings')->pluck('value', 'slug');
        } else { // if Laravel 5.1
            $installed_settings = DB::table('brazidesk_settings')->lists('value', 'slug');
        }

        if (!is_array($installed_settings)) {
            $installed_settings = $installed_settings->toArray();
        }

        // Application active migrations
        $default_Settings = $seeder->getDefaults();

        if (count($installed_settings) == count($default_Settings)) {
            return false;
        }

        $inactive_settings = array_diff_key($default_Settings, $installed_settings);

        return $inactive_settings;
    }

    /**
     * Generate demo users, agents, and tickets.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function demoDataSeeder()
    {
        $seeder = new BrazideskTableSeeder();
        $seeder->run();
        session()->flash('status', 'Demo tickets, users, and agents are seeded!');

        return redirect()->action('\Brazidev\Brazidesk\Controllers\TicketsController@index');
    }
}

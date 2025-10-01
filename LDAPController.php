<?php
namespace App\Http\Controllers;

use App\Models\AcadimicRank;
use App\Models\LDAP;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use LdapRecord\Auth\BindException;
use LdapRecord\Connection;
use LdapRecord\Container;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class LDAPController extends Controller
{
    public $connection;
    public $container;
    public $CheckConnection = true;
    public $MessageConnection = '';

    public function saveSettings(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'id' => 'nullable|exists:ldap_settings,id',
            'hosts' => 'required|string',
            'port' => 'required|integer',
            'base_dn' => 'required|string',
            'username' => 'required|string',
            'password' => [LDAP::first() ? null : 'required', 'nullable'],
            'filter' => 'nullable|string',
            'version' => 'required|integer',
            'timeout' => 'required|integer',
            'ssl' => 'nullable|in:true,false',
            'tls' => 'nullable|in:true,false',
            'follow' => 'nullable|in:true,false',
        ]);

        // Use the LDAP model to update or create the record
        LDAP::updateOrCreate(
            ['id' => $request->id],
            [
                'hosts' => $request->hosts,
                'port' => $request->port,
                'base_dn' => $request->base_dn,
                'username' => $request->username,
                'password' => Crypt::encrypt($request->password), // Hash password before saving
                'filter' => $request->filter,
                'version' => $request->version,
                'timeout' => $request->timeout,
                'ssl' => $request->boolean('ssl'),
                'tls' => $request->boolean('tls'),
                'follow' => $request->boolean('follow'),
            ]
        );

        return response()->json(['message' => 'تم حفظ الإعدادات بنجاح']);
    }

    public function ldapUsersData(Request $request)
    {
        // DataTables server-side parameters
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = trim($request->input('search.value', ''));

        // Get all LDAP users
        $allUsers = $this->getLdapUsers();

        // Build existing LDAP users lookup from DB
        $currentLDAPUsers = User::where('type', 'ldap')
            ->select('email', 'username')
            ->get()
            ->toArray();
        $currentLDAPLookup = collect($currentLDAPUsers)->pluck('email', 'username')->toArray();

        $recordsTotal = count($allUsers);

        // Filter
        if ($searchValue !== '') {
            $allUsers = array_values(array_filter($allUsers, function ($user) use ($searchValue) {
                $haystack = implode(' ', [
                    $user['username'] ?? '',
                    $user['name'] ?? '',
                    $user['ar_name'] ?? '',
                    $user['en_name'] ?? '',
                    $user['email'] ?? '',
                    $user['acadimic_rank'] ?? '',
                ]);
                return stripos($haystack, $searchValue) !== false;
            }));
        }

        $recordsFiltered = count($allUsers);

        // Pagination slice
        $pageData = array_slice($allUsers, $start, $length);

        // Append existence flag
        $data = array_map(function ($user) use ($currentLDAPLookup) {
            $user['exist'] = isset($currentLDAPLookup[$user['username']]) || in_array($user['email'], $currentLDAPLookup);
            $user['username'] = isset($user['username']) ? (string) $user['username'] : '';
            $user['name'] = isset($user['name']) ? (string) $user['name'] : '';
            $user['ar_name'] = isset($user['ar_name']) ? (string) $user['ar_name'] : '';
            $user['en_name'] = isset($user['en_name']) ? (string) $user['en_name'] : '';
            $user['email'] = isset($user['email']) ? (string) $user['email'] : '';
            $user['acadimic_rank'] = isset($user['acadimic_rank']) ? (string) $user['acadimic_rank'] : '';
            return $user;
        }, $pageData);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function testConnection(Request $request)
    {
        try {
            $base_dn = explode(",", $request->ldapSettings['base_dn']);
            $firstDcValue = null;
            foreach ($base_dn as $component) {
                if (strpos($component, "DC=") === 0) {
                    $firstDcValue = substr($component, 3);
                    break;
                }
            }
            $connection = new Connection([
                'hosts' => explode(',', $request->ldapSettings['hosts']),
                'port' => $request->ldapSettings['port'],
                'base_dn' => $request->ldapSettings['base_dn'],
                'username' => $firstDcValue . '\\' . $request->ldapSettings['username'],
                'password' => Crypt::decrypt($request->ldapSettings['password']),
                'use_ssl' => ($request->ldapSettings['ssl'] == 1) ? true : false,
                'use_tls' => ($request->ldapSettings['tls'] == 1) ? true : false,
                'follow_referrals' => ($request->follow == 1) ? true : false,
                'version' => (int) $request->ldapSettings['version'],
                'timeout' => (int) $request->ldapSettings['timeout'],
            ]);

            $connection->connect();
            $container = Container::addConnection($connection);
            $this->connection = $connection;
            $this->container = $container;

            return response()->json([
                'success' => true,
                'message' => 'LDAP Connection successful'
            ], 200);
        } catch (BindException $e) {
            $error = $e->getDetailedError();
            return response()->json([
                'success' => false,
                'error' => "Error: " . $error->getErrorMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Unexpected error: " . $e->getMessage()
            ], 500);
        }
    }

    public function LdapConnection()
    {
        $ldapSettings = LDAP::first();

        $base_dn = explode(",", $ldapSettings->base_dn);
        $firstDcValue = null;
        foreach ($base_dn as $component) {
            if (strpos($component, "DC=") === 0) {
                $firstDcValue = substr($component, 3);
                break;
            }
        }

        $connection = new Connection([
            'hosts' => explode(',', $ldapSettings->hosts),
            'port' => $ldapSettings->port,
            'base_dn' => $ldapSettings->base_dn,
            'username' => $firstDcValue . '\\' . $ldapSettings->username,
            'password' => Crypt::decrypt($ldapSettings->password),
            'use_ssl' => ($ldapSettings->ssl == '1'),
            'use_tls' => ($ldapSettings->tls == '1'),
            'version' => (int) $ldapSettings->version,
            'timeout' => (int) $ldapSettings->timeout,
            'follow_referrals' => ($ldapSettings->follow == '1'),
        ]);

        try {
            $connection->connect();
            $container = Container::addConnection($connection);
            $this->connection = $connection;
            $this->container = $container;

            return [
                'status' => true,
                'message' => 'LDAP connection successful',
            ];
        } catch (BindException $e) {
            $error = $e->getDetailedError();

            return [
                'status' => false,
                'error_code' => $error->getErrorCode(),
                'error_message' => $error->getErrorMessage(),
                'diagnostic_message' => $error->getDiagnosticMessage(),
            ];
        }
    }

    public function checkExistUserLdap($username)
    {
        try {
            $this->LdapConnection();

            if (!$this->connection) {
                throw new \Exception('Failed to connect with LDAP');
            }

            $user = $this->connection->query()->where('samaccountname', '=', $username)->first();

            $userAcademicRank = isset($user['title'][0]) ? (string) $user['title'][0] : null;

            $rankId = null;

            if ($userAcademicRank) {
                $existAcademicRank = AcadimicRank::firstOrCreate(
                    ['ar_name' => $userAcademicRank],
                    ['name' => $userAcademicRank]
                );

                $rankId = $existAcademicRank->id;
            }

            if ($user) {
                $userData = [
                    'username' => $username,
                    'acadimic_rank_id' => $rankId,
                    'name' => isset($user['givenname'][0]) ? (string) $user['givenname'][0] : '',
                    'ar_name' => isset($user['cn'][0]) ? (string) $user['cn'][0] : '',
                    'en_name' => isset($user['displayname'][0]) ? (string) $user['displayname'][0] : '',
                    'email' => isset($user['mail'][0]) ? (string) $user['mail'][0] : '',
                ];

                return $userData;
            }

            return 0;
        } catch (\Exception $e) {
            abort(500, $e->getMessage() ?: 'Failed to connect with LDAP');
        }
    }

    public function getLdapUsers()
    {
        try {
            $this->LdapConnection();

            if (!$this->connection) {
                throw new \Exception('Failed to connect with LDAP');
            }

            $users = $this->connection->query()->where('objectclass', '=', 'user')->get();

            $fillable = [];

            foreach ($users as $user) {
                $fillable[] = [
                    'username' => isset($user['samaccountname'][0]) ? (string) $user['samaccountname'][0] : '',
                    'acadimic_rank' => isset($user['title'][0]) ? (string) $user['title'][0] : '',
                    'name' => isset($user['givenname'][0]) ? (string) $user['givenname'][0] : '',
                    'ar_name' => isset($user['cn'][0]) ? (string) $user['cn'][0] : '',
                    'en_name' => isset($user['displayname'][0]) ? (string) $user['displayname'][0] : '',
                    'email' => isset($user['mail'][0]) ? (string) $user['mail'][0] : '',
                ];
            }

            return $fillable;
        } catch (\Exception $e) {
            abort(500, 'Failed to connect with LDAP');
        }
    }

    public function importUsers(Request $request)
    {
        $users = $request->input('users');
        $batchSize = 100;
        $totalImported = 0;
        $failedImports = [];
        $userRole = Role::where('name', 'Member')->first();

        try {
            DB::beginTransaction();

            foreach (array_chunk($users, $batchSize) as $usersBatch) {
                $usersToInsert = [];
                $userCredentials = [];
                $existingUsernames = [];

                $usernames = collect($usersBatch)->pluck('username')->toArray();

                $existingUsers = User::whereIn('username', $usernames)
                    ->orWhereIn('email', collect($usersBatch)->pluck('email')->toArray())
                    ->select('username', 'email')
                    ->get();

                $existingUsernamesIndex = $existingUsers->pluck('username')->flip()->toArray();
                $existingEmailsIndex = $existingUsers->pluck('email')->flip()->toArray();

                foreach ($usersBatch as $userData) {
                    if (
                        isset($existingUsernamesIndex[$userData['username']]) ||
                        isset($existingEmailsIndex[$userData['email']])
                    ) {
                        $failedImports[] = [
                            'username' => $userData['username'],
                            'reason' => __('Username or email already exists')
                        ];
                        continue;
                    }

                    $userAcademicRank = isset($userData['acadimic_rank']) ? (string) $userData['acadimic_rank'] : null;

                    $rankId = null;

                    if ($userAcademicRank) {
                        $existAcademicRank = AcadimicRank::firstOrCreate(
                            ['ar_name' => $userAcademicRank],
                            ['name' => $userAcademicRank]
                        );

                        $rankId = $existAcademicRank->id;
                    }

                    $usersToInsert[] = [
                        'username' => isset($userData['username']) ? (string) $userData['username'] : '',
                        'name' => isset($userData['name']) ? (string) $userData['name'] : '',
                        'ar_name' => isset($userData['ar_name']) ? (string) $userData['ar_name'] : '',
                        'en_name' => isset($userData['en_name']) ? (string) $userData['en_name'] : '',
                        'email' => isset($userData['email']) ? (string) $userData['email'] : '',
                        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                        'type' => 'ldap',
                        'is_active' => 1,
                        'position_id' => 1,
                        'acadimic_rank_id' => $rankId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $userCredentials[] = [
                        'username' => $userData['username'],
                        'email' => $userData['email']
                    ];

                    $existingUsernamesIndex[$userData['username']] = true;
                    $existingEmailsIndex[$userData['email']] = true;
                }

                if (!empty($usersToInsert)) {
                    User::insert($usersToInsert);

                    foreach ($userCredentials as $credentials) {
                        $user = User::where('username', $credentials['username'])
                            ->orWhere('email', $credentials['email'])
                            ->first();

                        if ($user) {
                            $user->assignRole($userRole);
                            $totalImported++;
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __("users were successfully imported") . " {$totalImported}.",
                'total_imported' => $totalImported,
                'failed_imports' => $failedImports,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to import users. ' . $e->getMessage(),
            ], 500);
        }
    }
}

<?php
namespace App\Http\Controllers;

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
            'id' => 'nullable|exists:ldap_settings,id', // Check if ID exists in the DB
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
            ['id' => $request->id], // If ID exists, update; if not, create a new record
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

    public function testConnection(Request $request)
    {

        try {

            $base_dn = explode(",", $request->ldapSettings['base_dn']);
            $firstDcValue = null;
            foreach ($base_dn as $component) {
                if (strpos($component, needle: "DC=") === 0) {
                    // Extract the value of the first "DC" component
                    $firstDcValue = substr($component, 3);
                    break;
                }
            }
            $connection = new Connection([
                'hosts' => explode(',', $request->ldapSettings['hosts']),
                'port' => $request->ldapSettings['port'],
                'base_dn' => $request->ldapSettings['base_dn'],
                'username' =>$firstDcValue . '\\' . $request->ldapSettings['username'],
                'password' => Crypt::decrypt($request->ldapSettings['password']),
                // Optional Configuration Options
                'use_ssl' => ($request->ldapSettings['ssl'] == 1)  ? true : false,
                'use_tls' => ($request->ldapSettings['tls'] == 1)  ? true : false,
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


        // Split the DN string by commas
        $base_dn = explode(",", $ldapSettings->base_dn);
        $firstDcValue = null;
        foreach ($base_dn as $component) {
            if (strpos($component, "DC=") === 0) {
                // Extract the value of the first "DC" component
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
            // Optional Configuration Options
            'use_ssl' => ($ldapSettings->ssl == '1') ? true : false,
            'use_tls' => ($ldapSettings->tls == '1') ? true : false,
            'version' => (int) $ldapSettings->version,
            'timeout' => (int) $ldapSettings->timeout,
            'follow_referrals' => ($ldapSettings->follow == '1') ? true : false,
        ]);

        try {
            $connection->connect();
            $container = Container::addConnection($connection);
            $this->connection = $connection;
            $this->container = $container;
        } catch (BindException $e) {

            $error = $e->getDetailedError();
            $error->getErrorCode();
            $error->getErrorMessage();
            $error->getDiagnosticMessage();

            return $error;
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

            if ($user) {
                return [
                    'name' => $user['cn'][0] ?? '',
                    'username' => $username,
                    'email' => $user['mail'][0] ?? '',
                    'phone' => $user['telephonenumber'][0] ?? '',
                ];
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
                    'name' => $user['cn'][0] ?? '',
                    'username' => $user['samaccountname'][0] ?? '',
                    'email' => $user['mail'][0] ?? '',
                    'phone' => $user['telephonenumber'][0] ?? '',
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
        $batchSize = 100; // Adjust based on your server's capabilities
        $totalImported = 0;
        $failedImports = [];
        $userRole = Role::where('name', 'Member')->first();

        try {
            DB::beginTransaction();

            // Process users in batches
            foreach (array_chunk($users, $batchSize) as $usersBatch) {
                $usersToInsert = [];
                $userCredentials = []; // Store user credentials for role assignment
                $existingUsernames = [];

                // Extract usernames to check for existing users
                $usernames = collect($usersBatch)->pluck('username')->toArray();

                // Check existing users in a single query
                $existingUsers = User::whereIn('username', $usernames)
                    ->orWhereIn('email', collect($usersBatch)->pluck('email')->toArray())
                    ->select('username', 'email')
                    ->get();

                // Create an index of existing usernames and emails for fast lookups
                $existingUsernamesIndex = $existingUsers->pluck('username')->flip()->toArray();
                $existingEmailsIndex = $existingUsers->pluck('email')->flip()->toArray();

                // Process each user in the batch
                foreach ($usersBatch as $userData) {
                    // Skip users that already exist
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

                    // Add to the batch for insertion
                    $usersToInsert[] = [
                        'username' => $userData['username'],
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'phone' => $userData['phone'] ?? null,
                        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                        'type' => 'ldap',
                        'is_active' => 1, // Activate the user by default
                        'position_id' => 1, // Default position being acacemic staff
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Store usernames to later assign roles
                    $userCredentials[] = [
                        'username' => $userData['username'],
                        'email' => $userData['email']
                    ];

                    // Add to our local tracking to prevent duplicates within the same request
                    $existingUsernamesIndex[$userData['username']] = true;
                    $existingEmailsIndex[$userData['email']] = true;
                }

                // Insert the batch if we have users to insert
                if (!empty($usersToInsert)) {
                    User::insert($usersToInsert);

                    // Now assign roles to the newly created users
                    foreach ($userCredentials as $credentials) {
                        // Find the user by username and assign the role
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

            // Return a response with the results
            return response()->json([
                'success' => true,
                'message' =>  __("users were successfully imported") . " {$totalImported}.",
                'total_imported' => $totalImported,
                'failed_imports' => $failedImports,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            report($e); // Log the exception

            return response()->json([
                'success' => false,
                'message' => 'Failed to import users. ' . $e->getMessage(),
            ], 500);
        }
    }

}

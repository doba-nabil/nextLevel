<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TableTruncateController extends Controller
{
    /**
     * Truncate a table by name
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function truncate(Request $request)
    {
        $tableName = $request->input('table') ?? $request->query('table');

        if (!$tableName) {
            return response()->json([
                'success' => false,
                'message' => 'Table name is required. Use ?table=table_name'
            ], 400);
        }

        // Sanitize table name to prevent SQL injection
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

        if (empty($tableName)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid table name'
            ], 400);
        }

        try {
            // Check if table exists
            if (!Schema::hasTable($tableName)) {
                return response()->json([
                    'success' => false,
                    'message' => "Table '{$tableName}' does not exist"
                ], 404);
            }

            // Get database driver
            $driver = DB::connection()->getDriverName();

            // Disable foreign key checks
            if (in_array($driver, ['mysql', 'mariadb'])) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            } elseif ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = replica;');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            }

            // Truncate the table
            DB::table($tableName)->truncate();

            // Re-enable foreign key checks
            if (in_array($driver, ['mysql', 'mariadb'])) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = DEFAULT;');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            }

            return response()->json([
                'success' => true,
                'message' => "Table '{$tableName}' has been truncated successfully"
            ], 200);

        } catch (\Exception $e) {
            // Make sure to re-enable foreign key checks even if there's an error
            try {
                $driver = DB::connection()->getDriverName();
                if (in_array($driver, ['mysql', 'mariadb'])) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } elseif ($driver === 'pgsql') {
                    DB::statement('SET session_replication_role = DEFAULT;');
                } elseif ($driver === 'sqlite') {
                    DB::statement('PRAGMA foreign_keys = ON;');
                }
            } catch (\Exception $e2) {
                // Ignore errors when re-enabling
            }

            return response()->json([
                'success' => false,
                'message' => 'Error truncating table: ' . $e->getMessage()
            ], 500);
        }
    }
}

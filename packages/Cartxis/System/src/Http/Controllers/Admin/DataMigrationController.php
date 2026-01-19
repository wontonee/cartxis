<?php

declare(strict_types=1);

namespace Cartxis\System\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class DataMigrationController extends Controller
{
    /**
     * Display the data migration dashboard
     */
    public function index(): Response
    {
        return Inertia::render('Admin/System/DataMigration/Index', [
            'sources' => [
                [
                    'id' => 'woocommerce',
                    'name' => 'WooCommerce',
                    'description' => 'Migrate products, customers, orders, and categories from WooCommerce',
                    'icon' => 'shopping-cart',
                    'configured' => $this->isWooCommerceConfigured(),
                ],
                [
                    'id' => 'bagisto',
                    'name' => 'Bagisto',
                    'description' => 'Migrate products, customers, orders, and categories from Bagisto',
                    'icon' => 'store',
                    'configured' => $this->isBagistoConfigured(),
                ],
            ],
        ]);
    }
    
    /**
     * Start migration process
     */
    public function migrate(Request $request)
    {
        $request->validate([
            'source' => 'required|in:woocommerce,bagisto',
            'entity' => 'required|in:all,categories,customers,products,orders',
            'dry_run' => 'boolean',
        ]);
        
        $source = $request->input('source');
        $entity = $request->input('entity');
        $dryRun = $request->boolean('dry_run', false);
        
        // Build command
        $command = $source === 'woocommerce' 
            ? "wc:migrate:{$entity}"
            : "bagisto:migrate:{$entity}";
        
        $options = $dryRun ? ['--dry-run' => true] : [];
        
        try {
            // Verify command exists first
            $allCommands = \Artisan::all();
            if (!isset($allCommands[$command])) {
                return response()->json([
                    'success' => false,
                    'message' => "Command '{$command}' not found. Available commands: " . implode(', ', array_keys($allCommands)),
                ], 500);
            }
            
            // Run migration in background
            Artisan::call($command, $options);
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => $dryRun 
                    ? 'Dry run completed successfully'
                    : 'Migration completed successfully',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get migration status
     */
    public function status(Request $request)
    {
        $request->validate([
            'source' => 'required|in:woocommerce,bagisto',
        ]);
        
        $source = $request->input('source');
        $config = config("{$source}-migration");
        
        return response()->json([
            'configured' => $source === 'woocommerce' 
                ? $this->isWooCommerceConfigured()
                : $this->isBagistoConfigured(),
            'connection' => $config['connection'] ?? null,
        ]);
    }
    
    /**
     * Test database connection
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'source' => 'required|in:woocommerce,bagisto',
        ]);
        
        $source = $request->input('source');
        $config = config("{$source}-migration");
        
        try {
            \DB::connection($config['connection'])->getPdo();
            
            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    private function isWooCommerceConfigured(): bool
    {
        $host = env('WOOCOMMERCE_DB_HOST');
        $database = env('WOOCOMMERCE_DB_DATABASE');
        
        return !empty($host) && !empty($database);
    }
    
    private function isBagistoConfigured(): bool
    {
        $host = env('BAGISTO_DB_HOST');
        $database = env('BAGISTO_DB_DATABASE');
        
        return !empty($host) && !empty($database);
    }
}

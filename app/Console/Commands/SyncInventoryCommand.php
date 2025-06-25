<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductInventory;

class SyncInventoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:sync {--fix : Fix inconsistencies automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and optionally fix inventory inconsistencies between products and batch data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting inventory consistency check...');
        
        $products = Product::all();
        $inconsistencies = 0;
        $fixedCount = 0;
        
        foreach ($products as $product) {
            $batchTotal = ProductInventory::getTotalStock($product->id);
            $productQuantity = $product->quantity;
            
            if ($batchTotal !== $productQuantity) {
                $inconsistencies++;
                $this->warn("Product ID {$product->id} ({$product->name}): Product quantity = {$productQuantity}, Batch total = {$batchTotal}");
                
                if ($this->option('fix')) {
                    $product->update(['quantity' => $batchTotal]);
                    $fixedCount++;
                    $this->info("  → Fixed: Updated product quantity to {$batchTotal}");
                }
            }
        }
        
        if ($inconsistencies === 0) {
            $this->info('✅ All inventory data is consistent!');
        } else {
            $this->warn("Found {$inconsistencies} inconsistencies.");
            
            if ($this->option('fix')) {
                $this->info("Fixed {$fixedCount} inconsistencies.");
            } else {
                $this->info("Run with --fix option to automatically correct inconsistencies.");
            }
        }
        
        return 0;
    }
} 
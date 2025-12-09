<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Permission;

class ProductsBreadSeeder extends Seeder
{
    public function run()
    {
        // Create Data Type
        $dataType = DataType::firstOrCreate([
            'slug' => 'products',
        ], [
            'name' => 'products',
            'display_name_singular' => 'Product',
            'display_name_plural' => 'Products',
            'icon' => 'voyager-bag',
            'model_name' => 'App\\Models\\Product',
            'policy_name' => null,
            'controller' => null,
            'description' => 'Product catalog management',
            'generate_permissions' => 1,
            'server_side' => 1,
            'details' => json_encode([
                'order_column' => 'created_at',
                'order_display_column' => 'name',
                'order_direction' => 'desc',
                'default_search_key' => 'name',
            ]),
        ]);

        // Create Data Rows (Fields)
        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'id',
        ], [
            'type' => 'number',
            'display_name' => 'ID',
            'required' => 1,
            'browse' => 1,
            'read' => 1,
            'edit' => 0,
            'add' => 0,
            'delete' => 0,
            'details' => null,
            'order' => 1,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'name',
        ], [
            'type' => 'text',
            'display_name' => 'Product Name',
            'required' => 1,
            'browse' => 1,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => json_encode(['validation' => ['required', 'max:255']]),
            'order' => 2,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'sku',
        ], [
            'type' => 'text',
            'display_name' => 'SKU',
            'required' => 0,
            'browse' => 1,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => json_encode(['validation' => ['max:100']]),
            'order' => 3,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'category',
        ], [
            'type' => 'text',
            'display_name' => 'Category',
            'required' => 0,
            'browse' => 1,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => json_encode(['validation' => ['max:100']]),
            'order' => 4,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'price',
        ], [
            'type' => 'number',
            'display_name' => 'Price',
            'required' => 0,
            'browse' => 1,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => json_encode([
                'validation' => ['numeric', 'min:0'],
                'step' => '0.01',
            ]),
            'order' => 5,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'stock_quantity',
        ], [
            'type' => 'number',
            'display_name' => 'Stock',
            'required' => 0,
            'browse' => 1,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => json_encode(['validation' => ['integer', 'min:0']]),
            'order' => 6,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'is_active',
        ], [
            'type' => 'checkbox',
            'display_name' => 'Active',
            'required' => 0,
            'browse' => 1,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => json_encode(['on' => 'Yes', 'off' => 'No', 'checked' => true]),
            'order' => 7,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'created_at',
        ], [
            'type' => 'timestamp',
            'display_name' => 'Created At',
            'required' => 0,
            'browse' => 1,
            'read' => 1,
            'edit' => 0,
            'add' => 0,
            'delete' => 0,
            'details' => null,
            'order' => 8,
        ]);

        // Hidden fields
        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'description',
        ], [
            'type' => 'text_area',
            'display_name' => 'Description',
            'required' => 0,
            'browse' => 0,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => null,
            'order' => 9,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'currency',
        ], [
            'type' => 'text',
            'display_name' => 'Currency',
            'required' => 0,
            'browse' => 0,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => json_encode(['default' => 'EGP']),
            'order' => 10,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'specifications',
        ], [
            'type' => 'text_area',
            'display_name' => 'Specifications (JSON)',
            'required' => 0,
            'browse' => 0,
            'read' => 1,
            'edit' => 1,
            'add' => 1,
            'delete' => 1,
            'details' => null,
            'order' => 11,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'department_id',
        ], [
            'type' => 'hidden',
            'display_name' => 'Department ID',
            'required' => 0,
            'browse' => 0,
            'read' => 0,
            'edit' => 0,
            'add' => 0,
            'delete' => 1,
            'details' => null,
            'order' => 12,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'import_id',
        ], [
            'type' => 'hidden',
            'display_name' => 'Import ID',
            'required' => 0,
            'browse' => 0,
            'read' => 0,
            'edit' => 0,
            'add' => 0,
            'delete' => 0,
            'details' => null,
            'order' => 13,
        ]);

        DataRow::firstOrCreate([
            'data_type_id' => $dataType->id,
            'field' => 'updated_at',
        ], [
            'type' => 'timestamp',
            'display_name' => 'Updated At',
            'required' => 0,
            'browse' => 0,
            'read' => 1,
            'edit' => 0,
            'add' => 0,
            'delete' => 0,
            'details' => null,
            'order' => 14,
        ]);

        // Create Menu Item
        $menu = Menu::where('name', 'admin')->first();
        if ($menu) {
            MenuItem::firstOrCreate([
                'menu_id' => $menu->id,
                'title' => 'Products',
            ], [
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-bag',
                'color' => null,
                'parent_id' => null,
                'order' => 10,
                'route' => 'voyager.products.index',
                'parameters' => null,
            ]);
        }

        // Create Permissions
        Permission::generateFor('products');

        $this->command->info('Products BREAD created successfully!');
    }
}

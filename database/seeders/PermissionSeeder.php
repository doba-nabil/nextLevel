<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $permissions = [
            // Roles
            ['name' => 'roles.index', 'guard_name' => 'web', 'display_name' => ['en' => 'View Roles', 'ar' => 'عرض الأدوار'], 'group' => 'roles', 'group_name' => ['en' => 'Roles', 'ar' => 'الأدوار']],
            ['name' => 'roles.create', 'guard_name' => 'web', 'display_name' => ['en' => 'Create Role', 'ar' => 'إضافة دور'], 'group' => 'roles', 'group_name' => ['en' => 'Roles', 'ar' => 'الأدوار']],
            ['name' => 'roles.edit', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit Role', 'ar' => 'تعديل دور'], 'group' => 'roles', 'group_name' => ['en' => 'Roles', 'ar' => 'الأدوار']],
            ['name' => 'roles.delete', 'guard_name' => 'web', 'display_name' => ['en' => 'Delete Role', 'ar' => 'حذف دور'], 'group' => 'roles', 'group_name' => ['en' => 'Roles', 'ar' => 'الأدوار']],

            // Users
            ['name' => 'users.index', 'guard_name' => 'web', 'display_name' => ['en' => 'View Users', 'ar' => 'عرض المستخدمين'], 'group' => 'users', 'group_name' => ['en' => 'Users', 'ar' => 'المستخدمين']],
            ['name' => 'users.create', 'guard_name' => 'web', 'display_name' => ['en' => 'Create User', 'ar' => 'إضافة مستخدم'], 'group' => 'users', 'group_name' => ['en' => 'Users', 'ar' => 'المستخدمين']],
            ['name' => 'users.edit', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit User', 'ar' => 'تعديل مستخدم'], 'group' => 'users', 'group_name' => ['en' => 'Users', 'ar' => 'المستخدمين']],
            ['name' => 'users.delete', 'guard_name' => 'web', 'display_name' => ['en' => 'Delete User', 'ar' => 'حذف مستخدم'], 'group' => 'users', 'group_name' => ['en' => 'Users', 'ar' => 'المستخدمين']],

            // Categories
            ['name' => 'categories.index', 'guard_name' => 'web', 'display_name' => ['en' => 'View Categories', 'ar' => 'عرض التصنيفات'], 'group' => 'categories', 'group_name' => ['en' => 'Categories', 'ar' => 'التصنيفات']],
            ['name' => 'categories.create', 'guard_name' => 'web', 'display_name' => ['en' => 'Create Category', 'ar' => 'إضافة تصنيف'], 'group' => 'categories', 'group_name' => ['en' => 'Categories', 'ar' => 'التصنيفات']],
            ['name' => 'categories.edit', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit Category', 'ar' => 'تعديل تصنيف'], 'group' => 'categories', 'group_name' => ['en' => 'Categories', 'ar' => 'التصنيفات']],
            ['name' => 'categories.delete', 'guard_name' => 'web', 'display_name' => ['en' => 'Delete Category', 'ar' => 'حذف تصنيف'], 'group' => 'categories', 'group_name' => ['en' => 'Categories', 'ar' => 'التصنيفات']],

            // Products
            ['name' => 'product.index', 'guard_name' => 'web', 'display_name' => ['en' => 'View Products', 'ar' => 'عرض المنتجات'], 'group' => 'products', 'group_name' => ['en' => 'Products', 'ar' => 'المنتجات']],
            ['name' => 'product.create', 'guard_name' => 'web', 'display_name' => ['en' => 'Create Product', 'ar' => 'إضافة منتج'], 'group' => 'products', 'group_name' => ['en' => 'Products', 'ar' => 'المنتجات']],
            ['name' => 'product.edit', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit Product', 'ar' => 'تعديل منتج'], 'group' => 'products', 'group_name' => ['en' => 'Products', 'ar' => 'المنتجات']],
            ['name' => 'product.delete', 'guard_name' => 'web', 'display_name' => ['en' => 'Delete Product', 'ar' => 'حذف منتج'], 'group' => 'products', 'group_name' => ['en' => 'Products', 'ar' => 'المنتجات']],

            // Admins
            ['name' => 'admins.index', 'guard_name' => 'web', 'display_name' => ['en' => 'View Admins', 'ar' => 'عرض المشرفين'], 'group' => 'admins', 'group_name' => ['en' => 'Admins', 'ar' => 'المشرفين']],
            ['name' => 'admins.create', 'guard_name' => 'web', 'display_name' => ['en' => 'Create Admin', 'ar' => 'إضافة مشرف'], 'group' => 'admins', 'group_name' => ['en' => 'Admins', 'ar' => 'المشرفين']],
            ['name' => 'admins.edit', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit Admin', 'ar' => 'تعديل مشرف'], 'group' => 'admins', 'group_name' => ['en' => 'Admins', 'ar' => 'المشرفين']],
            ['name' => 'admins.delete', 'guard_name' => 'web', 'display_name' => ['en' => 'Delete Admin', 'ar' => 'حذف مشرف'], 'group' => 'admins', 'group_name' => ['en' => 'Admins', 'ar' => 'المشرفين']],

            // Locations
            ['name' => 'locations.index', 'guard_name' => 'web', 'display_name' => ['en' => 'View Locations', 'ar' => 'عرض المواقع'], 'group' => 'locations', 'group_name' => ['en' => 'Locations', 'ar' => 'المواقع']],
            ['name' => 'locations.create', 'guard_name' => 'web', 'display_name' => ['en' => 'Create Location', 'ar' => 'إضافة موقع'], 'group' => 'locations', 'group_name' => ['en' => 'Locations', 'ar' => 'المواقع']],
            ['name' => 'locations.edit', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit Location', 'ar' => 'تعديل موقع'], 'group' => 'locations', 'group_name' => ['en' => 'Locations', 'ar' => 'المواقع']],
            ['name' => 'locations.delete', 'guard_name' => 'web', 'display_name' => ['en' => 'Delete Location', 'ar' => 'حذف موقع'], 'group' => 'locations', 'group_name' => ['en' => 'Locations', 'ar' => 'المواقع']],

            // Currencies
            ['name' => 'currencies.index', 'guard_name' => 'web', 'display_name' => ['en' => 'View Currencies', 'ar' => 'عرض العملات'], 'group' => 'currencies', 'group_name' => ['en' => 'Currencies', 'ar' => 'العملات']],
            ['name' => 'currencies.create', 'guard_name' => 'web', 'display_name' => ['en' => 'Create Currency', 'ar' => 'إضافة عملة'], 'group' => 'currencies', 'group_name' => ['en' => 'Currencies', 'ar' => 'العملات']],
            ['name' => 'currencies.edit', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit Currency', 'ar' => 'تعديل عملة'], 'group' => 'currencies', 'group_name' => ['en' => 'Currencies', 'ar' => 'العملات']],
            ['name' => 'currencies.delete', 'guard_name' => 'web', 'display_name' => ['en' => 'Delete Currency', 'ar' => 'حذف عملة'], 'group' => 'currencies', 'group_name' => ['en' => 'Currencies', 'ar' => 'العملات']],

            // Boxes
            ['name' => 'boxes.index', 'guard_name' => 'web', 'display_name' => ['en' => 'View Boxes', 'ar' => 'عرض الصناديق'], 'group' => 'boxes', 'group_name' => ['en' => 'Boxes', 'ar' => 'الوجبات']],
            ['name' => 'boxes.create', 'guard_name' => 'web', 'display_name' => ['en' => 'Create Box', 'ar' => 'إضافة صندوق'], 'group' => 'boxes', 'group_name' => ['en' => 'Boxes', 'ar' => 'الوجبات']],
            ['name' => 'boxes.edit', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit Box', 'ar' => 'تعديل صندوق'], 'group' => 'boxes', 'group_name' => ['en' => 'Boxes', 'ar' => 'الوجبات']],
            ['name' => 'boxes.delete', 'guard_name' => 'web', 'display_name' => ['en' => 'Delete Box', 'ar' => 'حذف صندوق'], 'group' => 'boxes', 'group_name' => ['en' => 'Boxes', 'ar' => 'الوجبات']],

            // Pages
            ['name' => 'pages.index', 'guard_name' => 'web', 'display_name' => ['en' => 'View Pages', 'ar' => 'عرض الصفحات'], 'group' => 'pages', 'group_name' => ['en' => 'Pages', 'ar' => 'الصفحات']],
            ['name' => 'pages.create', 'guard_name' => 'web', 'display_name' => ['en' => 'Create Page', 'ar' => 'إضافة صفحة'], 'group' => 'pages', 'group_name' => ['en' => 'Pages', 'ar' => 'الصفحات']],
            ['name' => 'pages.edit', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit Page', 'ar' => 'تعديل صفحة'], 'group' => 'pages', 'group_name' => ['en' => 'Pages', 'ar' => 'الصفحات']],
            ['name' => 'pages.delete', 'guard_name' => 'web', 'display_name' => ['en' => 'Delete Page', 'ar' => 'حذف صفحة'], 'group' => 'pages', 'group_name' => ['en' => 'Pages', 'ar' => 'الصفحات']],

            // Orders
            ['name' => 'orders.index', 'guard_name' => 'web', 'display_name' => ['en' => 'View Orders', 'ar' => 'عرض طلبات الشراء'], 'group' => 'orders', 'group_name' => ['en' => 'Orders', 'ar' => 'الطلبات']],
            ['name' => 'orders.edit', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit Order', 'ar' => 'تعديل حالة الطلب'], 'group' => 'orders', 'group_name' => ['en' => 'Orders', 'ar' => 'الطلبات']],
            ['name' => 'orders.delete', 'guard_name' => 'web', 'display_name' => ['en' => 'Delete Order', 'ar' => 'حذف الطلب'], 'group' => 'orders', 'group_name' => ['en' => 'Orders', 'ar' => 'الطلبات']],

            // Others
            ['name' => 'home', 'guard_name' => 'web', 'display_name' => ['en' => 'Show statistics', 'ar' => 'عرض الاحصائيات'], 'group' => 'statistics', 'group_name' => ['en' => 'Statistics', 'ar' => 'الاحصائيات']],
            ['name' => 'reports', 'guard_name' => 'web', 'display_name' => ['en' => 'Reports', 'ar' => 'التقارير'], 'group' => 'reports', 'group_name' => ['en' => 'Reports', 'ar' => 'التقارير']],
            ['name' => 'settings', 'guard_name' => 'web', 'display_name' => ['en' => 'Edit Settings', 'ar' => 'تعديل الإعدادات'], 'group' => 'settings', 'group_name' => ['en' => 'Settings', 'ar' => 'الاعدادات']],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']],
                ['display_name' => $permission['display_name'], 'group_name' => $permission['group_name'], 'group' => $permission['group']],
            );
        }
    }
}

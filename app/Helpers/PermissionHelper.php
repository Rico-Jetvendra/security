<?php

if (!function_exists('getMenuPermission')) {
    function getMenuPermission($routeName, $menus = null){
        $menus = $menus ?? config('combobox.menu');

        foreach ($menus as $menu) {
            // DIRECT MENU
            if (isset($menu['route']) && $menu['route'] === $routeName) {
                return $menu['permission'] ?? null;
            }

            // CHILD MENU
            if (isset($menu['children'])) {
                $permission = getMenuPermission($routeName, $menu['children']);

                if ($permission) {
                    return $permission;
                }
            }
        }

        return null;
    }
}


if (!function_exists('permission')) {
    function permission($action = null){
        $routeName = request()->route()->getName();

        // normalize datatable/ajax routes
        $routeName = preg_replace(
            '/\.(data|store|update|destroy|edit|create)$/',
            '.index',
            $routeName
        );

        $permission = getMenuPermission($routeName);

        if (!$permission) {
            return null;
        }

        return $action ? $permission.'.'.$action : $permission;
    }
}

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateUserPermissionsView extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW user_permissions_view AS
            SELECT 
                up.id AS id,
                up.user_id,
                up.permission_id,
                p.name AS permission_name,
                up.created_at,
                up.updated_at
            FROM user_permissions up
            JOIN permissions p ON up.permission_id = p.id
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS user_permissions_view");
    }
}

<?php

interface SOCOM_User_Interface {
    public function is_user_by_group(int|string $gid, ?int $id = null, array $deleted = [0]);

    public function user_can_admin();

    public function is_user(?int $id = null, array $deleted = [0]);

    public function get_user();

    public function get_users();

    public function activate_user(int $id, bool $auto_activate = false );

    public function set_user(int $id, int|string $gid);

    public function delete_user(int $id);

    public function save_user_history(int $id);
}
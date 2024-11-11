<?php
class DPW_RUI_Activator {
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Buat tabel anggota
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}dpw_rui_anggota (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            nomor_anggota varchar(20) NOT NULL,
            nama_perusahaan varchar(255) NOT NULL,
            pimpinan varchar(255) NOT NULL,
            alamat text NOT NULL,
            kabupaten varchar(100) NOT NULL,
            kode_pos varchar(10) NOT NULL,
            nomor_telpon varchar(20) NOT NULL,
            bidang_usaha varchar(255) NOT NULL, 
            nomor_ahu varchar(100) NOT NULL,
            jabatan varchar(100) NOT NULL,
            npwp varchar(30) NOT NULL,
            created_at datetime NOT NULL,
            created_by bigint(20) NOT NULL,
            updated_at datetime NOT NULL,
            updated_by bigint(20) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Tambah capabilities default untuk roles
        $admin = get_role('administrator');
        $admin->add_cap('dpw_rui_manage_all');
        
        // Buat roles baru
        add_role('dpw_rui_operator', 'DPW RUI Operator', array(
            'read' => true,
            'dpw_rui_create' => true,
            'dpw_rui_read' => true,
            'dpw_rui_update' => true, 
            'dpw_rui_edit_own' => true,
            'dpw_rui_view_list' => true
        ));
        
        add_role('dpw_rui_member', 'DPW RUI Member', array(
            'read' => true,
            'dpw_rui_read' => true,
            'dpw_rui_edit_own' => true,
            'dpw_rui_view_list' => true
        ));
    }
}
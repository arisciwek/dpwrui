<?php
/**
 * Path: /wp-content/plugins/dpwrui/includes/class-dpw-rui-validation.php
 * Version: 1.0.1
 * Date: 2024-11-16
 *
 * Changelog:
 * 1.0.1
 * - Fixed validate_required_fields to return WP_Error 
 * - Fixed validate_field_length to return WP_Error
 * - Fixed validation messaging
 * - Added proper error returns instead of wp_die
 */

class DPW_RUI_Validation {
    private $field_lengths = array(
        'nama_perusahaan' => 100,
        'pimpinan' => 100,
        'alamat' => 255,
        'kabupaten' => 50,
        'kode_pos' => 10,
        'nomor_telpon' => 20,
        'bidang_usaha' => 100,
        'nomor_ahu' => 50,
        'jabatan' => 50,
        'npwp' => 30,
        'nomor_anggota' => 20
    );

    private $required_fields = array(
        'nama_perusahaan' => 'Nama Perusahaan',
        'pimpinan' => 'Pimpinan',
        'alamat' => 'Alamat',
        'kabupaten' => 'Kabupaten',
        'nomor_telpon' => 'Nomor Telpon',
        'bidang_usaha' => 'Bidang Usaha',
        'nomor_ahu' => 'Nomor AHU',
        'npwp' => 'NPWP'
    );

    public function validate_required_fields($data) {
        $errors = array();
        foreach ($this->required_fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = sprintf('%s wajib diisi.', $label);
            }
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_failed', implode('<br>', $errors));
        }
        
        return true;
    }

    public function validate_field_length($data) {
        $errors = array();
        foreach ($data as $field => $value) {
            if (isset($this->field_lengths[$field]) && strlen($value) > $this->field_lengths[$field]) {
                $errors[] = sprintf(
                    'Field %s terlalu panjang. Maksimal %d karakter.',
                    $this->required_fields[$field] ?? $field,
                    $this->field_lengths[$field]
                );
            }
        }
        
        if (!empty($errors)) {
            return new WP_Error('validation_failed', implode('<br>', $errors));
        }
        
        return true;
    }

    public function truncate_fields($data) {
        foreach ($data as $field => $value) {
            if (isset($this->field_lengths[$field])) {
                $data[$field] = substr($value, 0, $this->field_lengths[$field]);
            }
        }
        return $data;
    }

    public function sanitize_form_data($post_data) {
        return array(
            'nama_perusahaan' => sanitize_text_field($post_data['nama_perusahaan']),
            'pimpinan' => sanitize_text_field($post_data['pimpinan']),
            'alamat' => sanitize_textarea_field($post_data['alamat']),
            'kabupaten' => sanitize_text_field($post_data['kabupaten']),
            'kode_pos' => sanitize_text_field($post_data['kode_pos']),
            'nomor_telpon' => sanitize_text_field($post_data['nomor_telpon']),
            'bidang_usaha' => sanitize_text_field($post_data['bidang_usaha']),
            'nomor_ahu' => sanitize_text_field($post_data['nomor_ahu']),
            'jabatan' => sanitize_text_field($post_data['jabatan']),
            'npwp' => sanitize_text_field($post_data['npwp']),
            'updated_at' => current_time('mysql'),
            'updated_by' => get_current_user_id()
        );
    }
}
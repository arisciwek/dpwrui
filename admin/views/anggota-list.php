<?php
/**
 * Path: /wp-content/plugins/dpwrui/admin/views/anggota-list.php
 * Version: 1.0.4
 * Timestamp: 2024-11-16 21:30:00
 * 
 * Changelog:
 * 1.0.4
 * - Fixed table width issues
 * - Improved grid structure
 * - Adjusted container layout
 * - Fixed responsive behavior
 */
?>

<div class="wrap">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Anggota DPW RUI</h1>
        <?php if(current_user_can('dpw_rui_create')): ?>
            <a href="<?php echo admin_url('admin.php?page=dpw-rui-add'); ?>" 
               class="d-none d-sm-inline-block btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50 mr-2"></i>
                Tambah Anggota
            </a>
        <?php endif; ?>
    </div>

    <?php
    $message = isset($_GET['message']) ? intval($_GET['message']) : 0;
    if($message): 
        $message_text = '';
        $message_class = 'alert-success';
        
        switch($message) {
            case 1:
                $message_text = 'Data anggota berhasil disimpan.';
                break;
            case 2:
                $message_text = 'Data anggota berhasil diupdate.';
                break;
            default:
                $message_text = 'Operasi berhasil dilakukan.';
        }
    ?>
        <div class="alert <?php echo $message_class; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?php echo esc_html($message_text); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Menggunakan container-fluid untuk full width -->
    <div class="container-fluid px-0">
        <div class="row">
            <!-- Menggunakan col-12 untuk full width -->
            <div class="col-12">
                <div class="card col-12 shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-users mr-2"></i>
                            Daftar Anggota
                        </h6>
                        <div class="d-none d-md-block">
                            <form method="post" class="form-inline">
                                <div class="input-group">
                                    <input type="text" 
                                           name="search" 
                                           class="form-control bg-light border-0 small" 
                                           placeholder="Cari anggota..."
                                           value="<?php echo esc_attr($search); ?>"
                                           aria-label="Search">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search fa-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Mobile Search -->
                        <div class="d-md-none mb-3">
                            <form method="post">
                                <div class="input-group">
                                    <input type="text" 
                                           name="search" 
                                           class="form-control bg-light border-0 small" 
                                           placeholder="Cari anggota..."
                                           value="<?php echo esc_attr($search); ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search fa-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No. Anggota</th>
                                        <th>Nama Perusahaan</th>
                                        <th>Nomor Telpon</th>
                                        <th class="text-center" style="width: 150px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($items): foreach($items as $item): ?>
                                        <tr>
                                            <td class="align-middle">
                                                <span class="font-weight-bold text-primary">
                                                    <?php echo esc_html($item->nomor_anggota); ?>
                                                </span>
                                            </td>
                                            <td class="align-middle"><?php echo esc_html($item->nama_perusahaan); ?></td>
                                            <td class="align-middle"><?php echo esc_html($item->nomor_telpon); ?></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <?php if(current_user_can('dpw_rui_read')): ?>
                                                        <a href="<?php echo admin_url('admin.php?page=dpw-rui&action=view&id=' . $item->id); ?>" 
                                                           class="btn btn-info btn-sm"
                                                           data-toggle="tooltip"
                                                           title="Lihat Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if(current_user_can('dpw_rui_update') || 
                                                             (current_user_can('dpw_rui_edit_own') && 
                                                              $item->created_by == get_current_user_id())): ?>
                                                        <a href="<?php echo add_query_arg(array(
                                                            'page' => 'dpw-rui',
                                                            'action' => 'edit',
                                                            'id' => $item->id
                                                        ), admin_url('admin.php')); ?>"
                                                           class="btn btn-primary btn-sm"
                                                           data-toggle="tooltip"
                                                           title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if(current_user_can('dpw_rui_delete')): ?>
                                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=dpw-rui&action=delete&id=' . $item->id), 
                                                                                        'delete_anggota_' . $item->id); ?>"
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Yakin ingin menghapus data ini?');"
                                                           data-toggle="tooltip"
                                                           title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    Tidak ada data anggota.
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if($total_pages > 1): ?>
                            <div class="row align-items-center mt-4">
                                <div class="col-sm-6">
                                    <p class="small text-muted">
                                        Menampilkan <?php echo count($items); ?> dari <?php echo $total_pages * 10; ?> data
                                    </p>
                                </div>
                                <div class="col-sm-6">
                                    <nav aria-label="Page navigation">
                                        <?php
                                        echo paginate_links(array(
                                            'base' => add_query_arg('paged', '%#%'),
                                            'format' => '',
                                            'prev_text' => '<i class="fas fa-chevron-left"></i>',
                                            'next_text' => '<i class="fas fa-chevron-right"></i>',
                                            'total' => $total_pages,
                                            'current' => $paged,
                                            'type' => 'list',
                                            'class' => 'pagination justify-content-end'
                                        ));
                                        ?>
                                    </nav>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Auto hide alerts
    $('.alert-dismissible').delay(3000).fadeOut(350);

    // Add hover effect to table rows
    $('.table-hover tbody tr').hover(
        function() {
            $(this).addClass('bg-light');
        },
        function() {
            $(this).removeClass('bg-light');
        }
    );
});
</script>
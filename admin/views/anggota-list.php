<div class="wrap">
    <h1 class="wp-heading-inline">Daftar Anggota DPW RUI</h1>
    
    <?php if(current_user_can('dpw_rui_create')): ?>
        <a href="<?php echo admin_url('admin.php?page=dpw-rui-add'); ?>" 
           class="page-title-action">Tambah Anggota</a>
    <?php endif; ?>

    <hr class="wp-header-end">

    <?php
    if(isset($_GET['message']) && $_GET['message'] == '1'): ?>
        <div class="updated notice is-dismissible">
            <p>Data anggota berhasil disimpan.</p>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Anggota</h6>
            <form method="post" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <div class="input-group">
                    <input type="text" name="search" class="form-control bg-light border-0 small" 
                           placeholder="Cari anggota..." value="<?php echo esc_attr($search); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No. Anggota</th>
                            <th>Nama Perusahaan</th>
                            <th>Nomor Telpon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($items): foreach($items as $item): ?>
                            <tr>
                                <td><?php echo esc_html($item->nomor_anggota); ?></td>
                                <td><?php echo esc_html($item->nama_perusahaan); ?></td>
                                <td><?php echo esc_html($item->nomor_telpon); ?></td>
                                <td>
                                    <?php if(current_user_can('dpw_rui_read')): ?>
                                        <a href="<?php echo admin_url('admin.php?page=dpw-rui&action=view&id=' . $item->id); ?>" 
                                           class="button button-small">
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
                                           class="button button-small" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if(current_user_can('dpw_rui_delete')): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=dpw-rui&action=delete&id=' . $item->id), 
                                                                        'delete_anggota_' . $item->id); ?>"
                                           class="button button-small"
                                           onclick="return confirm('Yakin ingin menghapus data ini?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data anggota.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($total_pages > 1): ?>
                <div class="pagination-wrap">
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $paged
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
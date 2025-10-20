<?php
/**
 * Shared table template for valid, corrected, and invalid VATs.
 * $title = section title
 * $rows = array of results
 */
?>

<div class="table-section">

    <?php if (!empty($rows)): ?>
        <?php
        // Generate a unique ID for this table so JS can target it
        $tableId = strtolower(str_replace(' ', '_', $title)) . '_table';
        ?>
        <div class="table-responsive shadow-sm">
            <table id="<?= $tableId ?>" class="table table-striped align-middle">
                <thead class="table-dark">
                <tr>
                    <?php if ($title == 'Valid VAT Numbers'): ?>
                        <th>Value</th>
                        <th>Status</th>
                    <?php else: ?>
                        <th>Final Value</th>
                        <th>Original Value</th>
                        <th>Status</th>
                    <?php endif; ?>
                    <?php if ($title == 'Invalid VAT Numbers'): ?>
                        <th>Correction / Error</th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <?php $status = $r['status'] ?? 'unknown'; ?>
                    <tr>
                        <td><?= htmlspecialchars($r['final_value'] ?? '') ?></td>
                        <?php if ($status && $status != 'valid'): ?>
                            <td><?= htmlspecialchars($r['original_value'] ?? '') ?></td>
                        <?php endif; ?>
                        <td>
                            <?php
                            switch ($status) {
                                case 'valid':
                                    $badgeClass = 'badge-valid';
                                    break;
                                case 'corrected':
                                    $badgeClass = 'badge-corrected';
                                    break;
                                case 'invalid':
                                    $badgeClass = 'badge-invalid';
                                    break;
                                default:
                                    $badgeClass = 'bg-secondary';
                                    break;
                            }
                            ?>
                            <span class="badge <?= $badgeClass ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </td>
                        <?php if ($status && $status == 'invalid'): ?>
                            <td><?= htmlspecialchars($r['correction_or_error'] ?? '') ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Initialize DataTables -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && $.fn.dataTable) {
                    $('#<?= $tableId ?>').DataTable({
                        pageLength: 10,
                        lengthMenu: [5, 10, 25, 50, 100],
                        order: [],
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries per page",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "No entries to show",
                            zeroRecords: "No matching records found",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        }
                    });
                }
            });
        </script>
    <?php endif; ?>
</div>

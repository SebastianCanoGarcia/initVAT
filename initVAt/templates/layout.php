<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Italian VAT Numbers App</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- DataTables + Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<style>
body { background-color: #f8f9fa; padding: 2rem; }
h1, h2 { color: #333; }
.badge-valid { background-color: #28a745; }
.badge-corrected { background-color: #ffc107; color: #000; }
.badge-invalid { background-color: #dc3545; }
.table-section { margin-top: 2rem; }
</style>
</head>

<body>
<div class="container">
<h1 class="mb-4 text-center">Italian VAT Numbers Validation</h1>

<!-- ===== Test Single VAT ===== -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Test Single VAT</h5>
        <form action="?action=test" method="post" class="d-flex gap-3">
            <input name="vat" class="form-control" placeholder="Enter VAT number" required>
            <button class="btn btn-success">Test</button>
        </form>
    </div>
</div>

<!-- ===== Botón para mostrar/ocultar CSV ===== -->
<div class="text-center mb-2">
    <button class="btn btn-outline-primary" id="toggleCsvBtn">Show / Hide CSV Upload</button>
</div>

<!-- ===== CSV Upload ===== -->
<div class="card mb-4" id="uploadCard">
    <div class="card-body">
        <h5 class="card-title">Upload CSV</h5>
        <form id="uploadForm" action="?action=upload" method="post" enctype="multipart/form-data" class="d-flex flex-column gap-3">
            <input type="file" name="csv" accept=".csv" class="form-control" required>
            <button type="submit" class="btn btn-primary w-25" id="uploadButton">Upload</button>

            <div class="progress mt-3" style="height: 25px; display:none;" id="progressContainer">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%" id="progressBar" aria-valuenow="0">
                  <span id="progress-text">
                    Uploading and processing...
                  </span>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- ===== Tables ===== -->
<?php
$uploadId = $_GET['uploadId'] ?? '';
$sections = [
    'Valid VAT Numbers' => $data['valid'] ?? [],
    'Corrected VAT Numbers' => $data['corrected'] ?? [],
    'Invalid VAT Numbers' => $data['invalid'] ?? []
];

foreach ($sections as $title => $rows) {
    $tableId = strtolower(str_replace(' ', '_', $title)) . '_table';
    ?>
    <div class="table-section card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="card-title"><?= htmlspecialchars($title) ?></h2>
                <button class="btn btn-sm btn-outline-secondary toggle-table-btn" data-target="<?= $tableId ?>">
                    Hide / Show Table
                </button>
            </div>
            <?php if (empty($rows)): ?>
                <p class="text-muted">No records found.</p>
            <?php else: ?>
                <div class="table-responsive" id="<?= $tableId ?>_container">
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
                                        case 'valid': $badgeClass = 'badge-valid'; break;
                                        case 'corrected': $badgeClass = 'badge-corrected'; break;
                                        case 'invalid': $badgeClass = 'badge-invalid'; break;
                                        default: $badgeClass = 'bg-secondary'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                                <?php if ($status && $status=='invalid'): ?>
                                    <td><?= htmlspecialchars($r['correction_or_error'] ?? '') ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php } ?>

</div>

<!-- JS Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function(){

    // Initialize all tables
    $('table').each(function(){
        if($.fn.dataTable) $(this).DataTable({pageLength:10, lengthMenu:[5,10,25,50,100], order:[]});
    });

    // Toggle tables visibility
    document.querySelectorAll('.toggle-table-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            const tableId = this.dataset.target + '_container';
            const container = document.getElementById(tableId);
            container.style.display = (container.style.display==='none') ? 'block' : 'none';
        });
    });

    // Toggle CSV upload visibility
    const toggleCsvBtn = document.getElementById('toggleCsvBtn');
    const uploadCard = document.getElementById('uploadCard');
    toggleCsvBtn.addEventListener('click', function(){
        uploadCard.style.display = (uploadCard.style.display==='none') ? 'block' : 'none';
    });

    // Upload form submit -> show spinner + progress
    <?php if (!empty($uploadId)) { ?>
      var uploadId = <?php echo json_encode($uploadId); ?>;
    <?php } ?>
    if(uploadId){
      const progress = document.getElementById('progressContainer');
      const button = document.getElementById('uploadButton');
      progress.style.display='block';
      button.disabled=true;
    }

    // ✅ Check progress function
    function updateProgress(pct) {
        const bar = document.getElementById('progressBar');
        const text = document.getElementById('progress-text');
        bar.style.width = pct + '%';
        bar.setAttribute('aria-valuenow', pct);
        if(text) text.innerText = pct + '%';
    }

    function checkProgress() {
        const url = new URL(window.location.href);
        url.searchParams.set('action', 'progress');
        url.searchParams.set('uploadId', uploadId);

        fetch(url.toString())
        .then(r => r.json())
        .then(d => {
            const pct = Math.floor((d.processed_rows / d.total_rows) * 100);
            updateProgress(pct);

            if (d.status !== 'done') setTimeout(checkProgress, 1000);
            else {
                if(document.getElementById('progress-text')) document.getElementById('progress-text').innerText = 'Process completed!';
                // Esperar 10 segundos antes de recargar
                setTimeout(() => { window.location.href = window.location.pathname; }, 3000);
            }
        })
        .catch(err => { console.error('Error fetching progress:', err); setTimeout(checkProgress, 2000); });
    }

    <?php if (!empty($uploadId)) { ?>
        var uploadId = <?php echo json_encode($uploadId); ?>;
        checkProgress();
    <?php } ?>
});
</script>
</body>
</html>

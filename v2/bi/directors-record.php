<?php
/** Exact read-only evidence for source rows without a native single-record inquiry. */
$active = 'bi_directors';
$AllowAnyone = true;
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__, 2) . '/bi/bootstrap.php';
header('Cache-Control: no-store, private');
function directorsH($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
try {
    $context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
    $dataset = isset($_GET['dataset']) && is_string($_GET['dataset']) ? $_GET['dataset'] : '';
    $id = isset($_GET['id']) && is_string($_GET['id']) ? $_GET['id'] : '';
    $currency = isset($_SESSION['CompanyRecord']['currencydefault']) ? $_SESSION['CompanyRecord']['currencydefault'] : 'Unknown';
    $evidence = (new \SAHamid\BI\Directors\DirectorsService($db, $context, $_SESSION, $currency))->record($dataset, $id);
} catch (\SAHamid\BI\Exception\BIException $e) {
    http_response_code($e->getHttpStatus());
    echo '<p>' . directorsH($e->getMessage()) . '</p>';
    exit;
} catch (\Throwable $e) {
    error_log('[directors-record] ' . $e->getMessage());
    http_response_code(503);
    echo '<p>The source record is temporarily unavailable.</p>';
    exit;
}
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/sidebar.php';
?>
<link rel="stylesheet" href="<?php echo directorsH($NewRootPath); ?>v2/bi/directors.css">
<div class="content-wrapper directors-page"><section class="content dir-content">
<div class="dir-section-heading"><div><h1><?php echo directorsH($evidence['title']); ?> — source evidence</h1>
<p><?php echo directorsH($evidence['company']); ?> · <?php echo directorsH($evidence['generated_at_utc']); ?> UTC · <?php echo directorsH($evidence['unit']); ?></p></div>
<a class="dir-button" href="<?php echo directorsH($NewRootPath); ?>v2/bi/directors.php">Directors’ Dashboard</a></div>
<div class="dir-definition"><strong>Definition:</strong> <?php echo directorsH($evidence['definition']); ?><br><?php echo directorsH($evidence['caveat']); ?></div>
<section class="dir-panel"><div class="dir-table-wrap"><table class="dir-table"><thead><tr><th>Source field</th><th>Recorded value</th></tr></thead><tbody>
<?php foreach ($evidence['row'] as $field => $value) { ?>
<tr><th scope="row"><?php echo directorsH(str_replace('_', ' ', $field)); ?></th><td><?php echo $value === null ? 'Unavailable' : directorsH($value); ?></td></tr>
<?php } ?>
</tbody></table></div></section></section></div>
<?php
require_once dirname(__DIR__) . '/includes/footer.php';
require_once dirname(__DIR__) . '/includes/foot.php';
?>

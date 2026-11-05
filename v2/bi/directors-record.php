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
<?php
$dashboardPath = $NewRootPath . 'v2/bi/directors.php';
$return = \SAHamid\BI\Directors\RecordPresenter::safeReturn(isset($_GET['directors_return']) ? $_GET['directors_return'] : '', $dashboardPath);
$groups = \SAHamid\BI\Directors\RecordPresenter::present($evidence['row'], $evidence['unit']);
?>
<div class="content-wrapper directors-page"><section class="content dir-content">
<div class="dir-section-heading"><div><div class="dir-eyebrow">SOURCE EVIDENCE</div><h1><?php echo directorsH($evidence['title']); ?></h1>
<p><?php echo directorsH($evidence['company']); ?> · row <?php echo directorsH(isset($evidence['row']['id']) ? $evidence['row']['id'] : ''); ?> · read <?php echo directorsH($evidence['generated_at_utc']); ?> UTC · <?php echo directorsH($evidence['unit']); ?></p></div>
<div class="dir-actions"><?php if ($return !== null) { ?><a class="dir-button dir-primary" href="<?php echo directorsH($return); ?>">← Back to the investigation</a><?php } ?><a class="dir-button" href="<?php echo directorsH($dashboardPath); ?>">Directors’ Dashboard</a><button class="dir-button" type="button" onclick="window.print()"><i class="fa fa-print" aria-hidden="true"></i> Print</button></div></div>
<div class="dir-definition"><strong>What this figure means:</strong> <?php echo directorsH($evidence['definition']); ?><br><strong>Limits:</strong> <?php echo directorsH($evidence['caveat']); ?></div>
<div class="dir-record-grid">
<?php foreach ($groups as $group) { ?>
<section class="dir-panel dir-record-group"><div class="dir-panel-heading"><div><h3><?php echo directorsH($group['title']); ?></h3></div></div>
<dl class="dir-kv">
<?php foreach ($group['fields'] as $field) { ?>
<div class="dir-kv-row"><dt><?php echo directorsH($field['label']); ?></dt><dd class="dir-kv-<?php echo directorsH($field['kind']); ?>"><?php echo directorsH($field['value']); ?></dd></div>
<?php } ?>
</dl></section>
<?php } ?>
</div>
<p class="dir-record-note">Values are shown as recorded in the source row. "Not recorded" means the source field is empty or a zero date; it does not mean zero.</p>
</section></div>
<?php
require_once dirname(__DIR__) . '/includes/footer.php';
require_once dirname(__DIR__) . '/includes/foot.php';
?>

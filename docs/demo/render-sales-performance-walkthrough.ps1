$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$demo = $PSScriptRoot
$framesDir = Join-Path $demo 'illustrative-frames'
$outputVideo = Join-Path $demo 'sales-performance-walkthrough.mp4'
$narration = Join-Path $demo 'sales-performance-walkthrough-narration.wav'
$captions = Join-Path $demo 'sales-performance-walkthrough.srt'
$ffmpeg = (Get-Command ffmpeg -ErrorAction Stop).Source
$ffprobe = (Get-Command ffprobe -ErrorAction Stop).Source

Add-Type -AssemblyName System.Drawing
New-Item -ItemType Directory -Force -Path $framesDir | Out-Null

$width = 1600
$height = 900
$navy = '#0D1729'
$canvas = '#F4F7FB'
$ink = '#182338'
$muted = '#66748A'
$blue = '#356AE6'
$teal = '#0F9F88'
$amber = '#CF8C19'
$red = '#CB5A57'
$line = '#E0E7F0'
$white = '#FFFFFF'

function Color([string]$value) {
    return [System.Drawing.ColorTranslator]::FromHtml($value)
}

function FillRect($graphics, [float]$x, [float]$y, [float]$w, [float]$h, [string]$color) {
    $brush = New-Object System.Drawing.SolidBrush((Color $color))
    $graphics.FillRectangle($brush, $x, $y, $w, $h)
    $brush.Dispose()
}

function StrokeRect($graphics, [float]$x, [float]$y, [float]$w, [float]$h, [string]$color, [float]$thickness = 1) {
    $pen = New-Object System.Drawing.Pen((Color $color), $thickness)
    $graphics.DrawRectangle($pen, $x, $y, $w, $h)
    $pen.Dispose()
}

function DrawText($graphics, [string]$value, [float]$x, [float]$y, [float]$w, [float]$h, [float]$size, [string]$color = '#182338', [bool]$bold = $false) {
    $style = [System.Drawing.FontStyle]::Regular
    if ($bold) { $style = [System.Drawing.FontStyle]::Bold }
    $font = New-Object System.Drawing.Font('Segoe UI', $size, $style, [System.Drawing.GraphicsUnit]::Pixel)
    $brush = New-Object System.Drawing.SolidBrush((Color $color))
    $rect = New-Object System.Drawing.RectangleF($x, $y, $w, $h)
    $format = New-Object System.Drawing.StringFormat
    $format.Alignment = [System.Drawing.StringAlignment]::Near
    $format.LineAlignment = [System.Drawing.StringAlignment]::Center
    $format.Trimming = [System.Drawing.StringTrimming]::EllipsisWord
    $graphics.DrawString($value, $font, $brush, $rect, $format)
    $format.Dispose()
    $brush.Dispose()
    $font.Dispose()
}

function DrawPill($graphics, [string]$label, [float]$x, [float]$y, [float]$w, [string]$fill, [string]$foreground) {
    FillRect $graphics $x $y $w 34 $fill
    DrawText $graphics $label ($x + 12) ($y + 2) ($w - 24) 30 14 $foreground $true
}

function DrawCard($graphics, [float]$x, [float]$y, [float]$w, [float]$h, [string]$title, [string]$value, [string]$detail = '', [string]$accent = '#356AE6') {
    FillRect $graphics $x $y $w $h '#FFFFFF'
    StrokeRect $graphics $x $y $w $h '#E0E7F0'
    FillRect $graphics $x $y 5 $h $accent
    DrawText $graphics $title ($x + 20) ($y + 15) ($w - 38) 30 16 '#66748A' $true
    DrawText $graphics $value ($x + 20) ($y + 51) ($w - 38) 42 25 '#182338' $true
    if ($detail) { DrawText $graphics $detail ($x + 20) ($y + 96) ($w - 38) 34 14 '#66748A' $false }
}

function DrawShell($graphics, [int]$sceneNumber, [string]$subtitle, [string]$activeItem = 'Sales Performance') {
    $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $graphics.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit
    FillRect $graphics 0 0 $width $height $canvas
    FillRect $graphics 0 0 256 $height $navy
    FillRect $graphics 0 0 $width 78 '#FFFFFF'
    FillRect $graphics 256 77 ($width - 256) 1 $line
    FillRect $graphics 28 25 34 34 $blue
    DrawText $graphics 'SA' 28 26 34 31 15 '#FFFFFF' $true
    DrawText $graphics 'SA Hamid' 74 20 150 30 19 '#FFFFFF' $true
    DrawText $graphics 'ENTERPRISE ERP' 74 48 150 20 11 '#AAB8CE' $true
    DrawText $graphics 'Workspace' 26 111 200 24 13 '#8190A8' $true

    $navItems = @('Overview', 'Sales', 'Inventory', 'Procurement', 'Reports')
    $navY = 151
    foreach ($item in $navItems) {
        if ($item -eq 'Sales') {
            FillRect $graphics 13 ($navY - 4) 230 42 '#192841'
            FillRect $graphics 13 ($navY - 4) 4 42 '#69A3FF'
        }
        DrawText $graphics $item 31 $navY 194 32 16 $(if ($item -eq 'Sales') { '#FFFFFF' } else { '#AFBDD1' }) ($item -eq 'Sales')
        $navY += 49
    }
    FillRect $graphics 27 407 198 1 '#26354C'
    DrawText $graphics 'Sales Performance' 31 426 196 30 15 '#FFFFFF' $true
    DrawText $graphics 'Month, week and team reporting' 31 458 203 44 12 '#AAB8CE' $false
    DrawText $graphics 'SIGNED-IN VIEW' 27 760 200 20 11 '#8190A8' $true
    DrawText $graphics 'Access follows ERP permissions' 27 785 205 38 12 '#AAB8CE' $false

    DrawText $graphics 'ERP  /  Reports  /  Sales Performance' 292 20 900 30 14 '#66748A' $false
    DrawText $graphics 'Sales Performance' 292 99 760 40 30 $ink $true
    DrawText $graphics $subtitle 292 143 960 32 16 $muted $false
    DrawPill $graphics 'ERP CONNECTED' 1275 100 146 '#E2F5F0' '#147D6C'
    DrawPill $graphics 'ILLUSTRATIVE UI' 1429 100 136 '#EEF3FF' '#2F5FC4'
    DrawText $graphics ('JOURNEY  ' + $sceneNumber.ToString('00') + ' / 10') 1364 146 193 24 12 '#66748A' $true
}

function DrawSectionHeading($graphics, [string]$title, [string]$hint, [float]$x, [float]$y, [float]$w) {
    DrawText $graphics $title $x $y $w 29 18 $ink $true
    DrawText $graphics $hint $x ($y + 29) $w 24 13 $muted $false
}

function DrawTable($graphics, [float]$x, [float]$y, [float]$w, [string[]]$headers, [string[][]]$rows, [float]$rowHeight = 42) {
    $cols = $headers.Count
    $colW = $w / $cols
    FillRect $graphics $x $y $w 38 '#EDF2F8'
    for ($c = 0; $c -lt $cols; $c++) {
        DrawText $graphics $headers[$c] ($x + 10 + ($c * $colW)) ($y + 2) ($colW - 18) 34 12 '#65748A' $true
    }
    for ($r = 0; $r -lt $rows.Count; $r++) {
        $rowY = $y + 38 + ($r * $rowHeight)
        FillRect $graphics $x $rowY $w $rowHeight '#FFFFFF'
        FillRect $graphics $x $rowY $w 1 '#E7EDF4'
        for ($c = 0; $c -lt $cols; $c++) {
            DrawText $graphics $rows[$r][$c] ($x + 10 + ($c * $colW)) ($rowY + 3) ($colW - 18) ($rowHeight - 6) 13 $(if ($c -eq 0) { '#233149' } else { '#66748A' }) ($c -eq 0)
        }
    }
}

$scenes = @(
    @{ Id='01-intro'; Title='Opening: a live ERP report'; Subtitle='One reporting journey, with clear source and coverage notes.'; Kind='intro' },
    @{ Id='02-scope-filters'; Title='Choose a period and authorized scope'; Subtitle='Set periods and filters to the audience and question.'; Kind='filters' },
    @{ Id='03-month-view'; Title='Read the monthly picture'; Subtitle='Review summary measures, daily activity and comparisons.'; Kind='month' },
    @{ Id='04-week-team'; Title='Move from weeks to team patterns'; Subtitle='Compare weekly activity and inspect team attribution.'; Kind='weekly' },
    @{ Id='05-source-detail'; Title='Inspect the supporting records'; Subtitle='Trace totals to source facts while protecting record details.'; Kind='source' },
    @{ Id='06-activity-capture'; Title='Enter and save the activity forms'; Subtitle='Follow one clearly tagged test-case flow through enquiry and follow-up forms.'; Kind='capture' },
    @{ Id='07-coverage-setup'; Title='Record a demo PO and review coverage'; Subtitle='Count a demo receipt without inventing an amount; keep coverage review separate.'; Kind='coverage' },
    @{ Id='08-reference-review'; Title='Handle source comparisons carefully'; Subtitle='Keep imported observations unverified until reviewed.'; Kind='reference' },
    @{ Id='09-freeze-export'; Title='Freeze and export one consistent report'; Subtitle='Save the selected scope and source facts as an auditable snapshot.'; Kind='freeze' },
    @{ Id='10-close'; Title='Close: what the report means today'; Subtitle='Real database connection; controlled scope; definitions still provisional.'; Kind='close' }
)

$sceneIndex = 0
foreach ($scene in $scenes) {
    $sceneIndex++
    $bitmap = New-Object System.Drawing.Bitmap($width, $height)
    $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
    try {
        DrawShell $graphics $sceneIndex $scene.Subtitle
        switch ($scene.Kind) {
            'intro' {
                DrawPill $graphics 'COMPANY REPORT' 292 201 180 '#EEF3FF' '#2F5FC4'
                DrawText $graphics 'A single place to review sales activity' 292 245 1050 44 25 $ink $true
                DrawText $graphics 'Start with the period, understand the source, then save the report you reviewed.' 292 292 1120 30 16 $muted $false
                DrawCard $graphics 292 352 300 137 'ENQUIRIES' 'Live report measure' 'Coverage varies by source history' $blue
                DrawCard $graphics 612 352 300 137 'QUOTATIONS' 'Live report measure' 'Raw and normalized shown separately' $teal
                DrawCard $graphics 932 352 300 137 'CUSTOMER PO RECEIPTS' 'Captured receipts' 'Kept separate from OC proxy' $amber
                DrawCard $graphics 1252 352 312 137 'DELIVERY CHALLANS' 'Live report measure' 'Source amount status is visible' $blue
                FillRect $graphics 292 528 1272 177 '#FFFFFF'; StrokeRect $graphics 292 528 1272 177 $line
                DrawText $graphics 'A careful reading of the report' 318 547 560 32 18 $ink $true
                DrawText $graphics 'Live ERP source records' 318 597 300 30 15 $muted $false
                DrawPill $graphics 'SOURCE-BACKED' 599 595 150 '#E2F5F0' '#147D6C'
                DrawText $graphics 'Metric definitions and historical coverage' 822 597 430 30 15 $muted $false
                DrawPill $graphics 'REVIEW REQUIRED' 1233 595 183 '#FFF2D9' '#95620E'
            }
            'filters' {
                DrawSectionHeading $graphics 'Reporting scope' 'Filters narrow what is shown; they do not expand a viewer access.' 292 202 900
                $filterCards = @(
                    @{X=292;Y=266;W=385;T='Reporting period';V='Choose start and end dates';C=$blue},
                    @{X=693;Y=266;W=385;T='Compare with';V='Choose a comparison period';C=$teal},
                    @{X=1094;Y=266;W=470;T='Historical cutoff';V='Set report-through date';C=$amber},
                    @{X=292;Y=386;W=385;T='Approved team population';V='Select an approved mapping';C=$blue},
                    @{X=693;Y=386;W=385;T='Teams';V='Limit to authorized teams';C=$teal},
                    @{X=1094;Y=386;W=470;T='Salespeople';V='Limit to authorized people';C=$amber}
                )
                foreach ($f in $filterCards) { DrawCard $graphics $f.X $f.Y $f.W 92 $f.T $f.V '' $f.C }
                FillRect $graphics 292 510 1272 128 '#FFFFFF'; StrokeRect $graphics 292 510 1272 128 $line
                DrawPill $graphics 'SERVER-ENFORCED SCOPE' 316 528 239 '#E2F5F0' '#147D6C'
                DrawText $graphics 'Named team populations require a separate approval before they appear here.' 316 575 1160 37 16 $ink $false
                FillRect $graphics 292 661 1272 56 '#FFF7E8'
                DrawText $graphics 'A named population cannot be combined with direct team or salesperson filters.' 316 671 1215 34 15 '#855A12' $true
            }
            'month' {
                DrawPill $graphics 'MONTH VIEW' 292 201 132 '#EEF3FF' '#2F5FC4'
                DrawPill $graphics 'WEEKLY OVERVIEW' 434 201 176 '#F1F4F8' '#596A80'
                DrawPill $graphics 'TEAM COMPARISON' 620 201 175 '#F1F4F8' '#596A80'
                DrawCard $graphics 292 252 296 100 'INITIAL QUOTATIONS' 'Live count' 'Value status visible' $blue
                DrawCard $graphics 606 252 296 100 'REVISED QUOTATIONS' 'Live count' 'Value status visible' $teal
                DrawCard $graphics 920 252 296 100 'CUSTOMER PO RECEIPTS' 'Captured count' 'Actual receipt events' $amber
                DrawCard $graphics 1234 252 330 100 'OCS (PROXY)' 'Separate measure' 'Not customer PO receipt' $red
                FillRect $graphics 292 378 770 340 '#FFFFFF'; StrokeRect $graphics 292 378 770 340 $line
                DrawSectionHeading $graphics 'Daily activity' 'Source coverage and value status are shown alongside each measure.' 316 397 690
                DrawTable $graphics 316 469 720 @('Date','Enquiries','Initial quotes','Follow-ups') @(
                    ,@('Reporting day','Live','Live','Captured history'),
                    ,@('Reporting day','Live','Live','Coverage note'),
                    ,@('Reporting day','Live','Live','Coverage note'),
                    ,@('Reporting day','Live','Live','Coverage note')
                ) 46
                FillRect $graphics 1082 378 482 340 '#FFFFFF'; StrokeRect $graphics 1082 378 482 340 $line
                DrawSectionHeading $graphics 'Daily activity trend' 'Values follow the selected period.' 1106 397 430
                FillRect $graphics 1120 492 420 184 '#F8FAFD'
                FillRect $graphics 1160 602 28 48 '#84A8F4'; FillRect $graphics 1211 566 28 84 '#4E7CE4'; FillRect $graphics 1262 579 28 71 '#70CBB7'
                FillRect $graphics 1313 529 28 121 '#356AE6'; FillRect $graphics 1364 554 28 96 '#80A5EF'; FillRect $graphics 1415 514 28 136 '#0F9F88'; FillRect $graphics 1466 540 28 110 '#9BB7F3'
                DrawText $graphics 'Week 1' 1152 654 90 21 12 $muted; DrawText $graphics 'Week 2' 1306 654 90 21 12 $muted; DrawText $graphics 'Week 3' 1450 654 90 21 12 $muted
                DrawPill $graphics 'PROVISIONAL SOURCE TOTALS' 1106 685 258 '#FFF2D9' '#95620E'
            }
            'weekly' {
                DrawPill $graphics 'MONTH VIEW' 292 201 132 '#F1F4F8' '#596A80'
                DrawPill $graphics 'WEEKLY OVERVIEW' 434 201 176 '#EEF3FF' '#2F5FC4'
                DrawPill $graphics 'TEAM COMPARISON' 620 201 175 '#F1F4F8' '#596A80'
                FillRect $graphics 292 252 782 450 '#FFFFFF'; StrokeRect $graphics 292 252 782 450 $line
                DrawSectionHeading $graphics 'Weekly overview' 'Weeks run Monday to Sunday; incomplete weeks remain visible.' 316 271 740
                DrawTable $graphics 316 344 734 @('Week','Enquiries','Quotes','Receipts','OC proxy') @(
                    ,@('Week A','Live','Live','Captured','Separate'),
                    ,@('Week B','Live','Live','Captured','Separate'),
                    ,@('Week C','Live','Live','Captured','Separate'),
                    ,@('Week D','Live','Live','Captured','Separate'),
                    ,@('Week E','Live','Live','Captured','Separate')
                ) 47
                FillRect $graphics 1094 252 470 450 '#FFFFFF'; StrokeRect $graphics 1094 252 470 450 $line
                DrawSectionHeading $graphics 'Team comparison' 'Ambiguous owner attribution stays unassigned.' 1118 271 420
                DrawPill $graphics 'WEEKLY TEAM VIEW' 1118 341 189 '#EEF3FF' '#2F5FC4'
                DrawTable $graphics 1118 392 422 @('Team','Activity','Coverage') @(
                    ,@('Team A','Live','Review status'),
                    ,@('Team B','Live','Review status'),
                    ,@('Team C','Live','Review status'),
                    ,@('Unassigned','Separate','Shown separately')
                ) 49
                DrawText $graphics 'Daily averages use Monday–Saturday reporting days.' 1118 647 420 38 13 $muted $false
            }
            'source' {
                DrawSectionHeading $graphics 'Quotation supporting documents' 'Inspect source facts without putting customer or document identifiers in the recording.' 292 202 1200
                DrawPill $graphics 'DETAIL DRILLDOWN' 292 263 173 '#EEF3FF' '#2F5FC4'
                FillRect $graphics 292 319 1272 377 '#FFFFFF'; StrokeRect $graphics 292 319 1272 377 $line
                DrawTable $graphics 316 350 1225 @('Source','Date basis','Value status','Tax basis') @(
                    ,@('Record hidden','ERP date proxy','Partial source facts','Provisional'),
                    ,@('Record hidden','Captured issue event','Reviewed facts','Source backed'),
                    ,@('Record hidden','ERP date proxy','Unknown stays unavailable','Provisional'),
                    ,@('Record hidden','ERP date proxy','Other currency not converted','Unavailable'),
                    ,@('Record hidden','Captured issue event','Source detail available','Source backed')
                ) 52
                DrawText $graphics 'Ordinary quotations use option-zero line details; quick quotations use recorded price and quantity.' 316 659 1200 28 14 $muted $false
                DrawPill $graphics 'NO CUSTOMER IDENTIFIERS SHOWN' 1272 715 268 '#E2F5F0' '#147D6C'
            }
            'capture' {
                DrawSectionHeading $graphics 'Enter and save activity forms' 'A test case demonstrates the form flow; every new entry is labeled DEMO ONLY.' 292 202 1200
                DrawPill $graphics 'DEMO-ONLY TEST CASE' 1330 202 234 '#FFF2D9' '#95620E'
                $captureCards = @(
                    @{X=292; W=402; Title='Enquiry event · receipt'; Event='Enquiry received'; Stamp='12 Oct 2026 · 09:15'; Detail='Evidence: DEMO ONLY'},
                    @{X=715; W=402; Title='Enquiry event · response'; Event='Customer contacted'; Stamp='12 Oct 2026 · 09:25'; Detail='Response type selected'},
                    @{X=1138; W=426; Title='Follow-up activity'; Event='Call · next action set'; Stamp='12 Oct 2026 · 09:35'; Detail='Notes: DEMO ONLY'}
                )
                foreach ($card in $captureCards) {
                    FillRect $graphics $card.X 262 $card.W 284 '#FFFFFF'; StrokeRect $graphics $card.X 262 $card.W 284 $line
                    DrawText $graphics $card.Title ($card.X + 20) 278 ($card.W - 40) 29 16 $ink $true
                    DrawText $graphics 'Sales case reference' ($card.X + 20) 318 ($card.W - 40) 21 12 $muted $true
                    FillRect $graphics ($card.X + 20) 342 ($card.W - 40) 34 '#FBFCFE'; StrokeRect $graphics ($card.X + 20) 342 ($card.W - 40) 34 $line
                    DrawText $graphics 'DEMO-CASE-01' ($card.X + 32) 344 ($card.W - 64) 30 13 $ink $false
                    DrawText $graphics 'Event / activity' ($card.X + 20) 385 ($card.W - 40) 21 12 $muted $true
                    FillRect $graphics ($card.X + 20) 409 ($card.W - 40) 34 '#FBFCFE'; StrokeRect $graphics ($card.X + 20) 409 ($card.W - 40) 34 $line
                    DrawText $graphics $card.Event ($card.X + 32) 411 ($card.W - 64) 30 13 $ink $false
                    DrawText $graphics 'Date and time' ($card.X + 20) 452 ($card.W - 40) 21 12 $muted $true
                    FillRect $graphics ($card.X + 20) 476 ($card.W - 40) 34 '#FBFCFE'; StrokeRect $graphics ($card.X + 20) 476 ($card.W - 40) 34 $line
                    DrawText $graphics $card.Stamp ($card.X + 32) 478 ($card.W - 64) 30 13 $ink $false
                    DrawPill $graphics 'SAVED · DEMO ONLY' ($card.X + 20) 516 ([Math]::Min(205, $card.W - 40)) '#E2F5F0' '#147D6C'
                }
                FillRect $graphics 292 576 1272 151 '#FFFFFF'; StrokeRect $graphics 292 576 1272 151 $line
                DrawText $graphics 'Saved case timeline' 316 587 510 28 17 $ink $true
                DrawTable $graphics 316 623 1224 @('Time','Saved entry','Evidence status') @(
                    ,@('09:15','Enquiry received','DEMO ONLY'),
                    ,@('09:25','Customer-contact response','DEMO ONLY'),
                    ,@('09:35','Call follow-up and next action','DEMO ONLY')
                ) 26
                DrawPill $graphics 'LOCAL REPORTING DATA · NO ERP TRANSACTION EDITED' 316 744 452 '#EEF3FF' '#2F5FC4'
            }
            'coverage' {
                DrawSectionHeading $graphics 'Customer PO receipt and coverage review' 'Show the saved demo receipt, then a separate coverage-review draft.' 292 202 1200
                FillRect $graphics 292 266 610 430 '#FFFFFF'; StrokeRect $graphics 292 266 610 430 $line
                DrawText $graphics 'Customer PO receipt' 316 283 550 32 18 $ink $true
                DrawPill $graphics 'SAVED · DEMO ONLY' 316 325 206 '#E2F5F0' '#147D6C'
                $poRows = @(
                    @('Sales case reference','DEMO-CASE-01'),
                    @('Customer PO reference','DEMO-ONLY-PO-20261012-01'),
                    @('Received date / time','12 Oct 2026 · 10:15'),
                    @('Amount','Blank · unavailable'),
                    @('Evidence','DEMO ONLY · test data')
                )
                $py = 374
                foreach ($row in $poRows) {
                    DrawText $graphics $row[0] 316 $py 190 25 12 $muted $true
                    FillRect $graphics 508 $py 366 31 '#FBFCFE'; StrokeRect $graphics 508 $py 366 31 $line
                    DrawText $graphics $row[1] 520 ($py + 1) 340 28 13 $ink $false
                    $py += 52
                }
                FillRect $graphics 922 266 642 430 '#FFFFFF'; StrokeRect $graphics 922 266 642 430 $line
                DrawText $graphics 'Reporting coverage' 946 283 580 32 18 $ink $true
                DrawText $graphics 'Review the calculated totals before submission.' 946 322 580 25 13 $muted $false
                $coverageRows = @(
                    @('Date','12 Oct 2026'),
                    @('Scope','Whole company'),
                    @('State','Partial'),
                    @('Evidence note','Historical event coverage remains incomplete.')
                )
                $cy = 372
                foreach ($row in $coverageRows) {
                    DrawText $graphics $row[0] 946 $cy 142 25 12 $muted $true
                    FillRect $graphics 1094 $cy 440 34 '#FBFCFE'; StrokeRect $graphics 1094 $cy 440 34 $line
                    DrawText $graphics $row[1] 1106 ($cy + 2) 416 30 13 $ink $false
                    $cy += 55
                }
                DrawPill $graphics 'DRAFT · NOT SUBMITTED' 946 606 235 '#FFF2D9' '#95620E'
                DrawText $graphics 'A demo activity sample cannot certify whole-company completeness.' 946 653 574 27 13 $muted $false
                DrawPill $graphics 'NO AMOUNT INVENTED' 316 715 218 '#EEF3FF' '#2F5FC4'
                DrawPill $graphics 'HISTORICAL COVERAGE STILL INCOMPLETE' 550 715 375 '#FFF2D9' '#95620E'
            }
            'reference' {
                DrawSectionHeading $graphics 'Reference observations and reconciliation' 'Preserve source, scope and review state when comparing a prior report.' 292 202 1200
                FillRect $graphics 292 270 767 397 '#FFFFFF'; StrokeRect $graphics 292 270 767 397 $line
                DrawText $graphics 'Source observation' 316 290 620 31 18 $ink $true
                DrawText $graphics 'Source version, evidence location and exact period' 316 332 710 30 14 $muted $false
                DrawPill $graphics 'TRANSCRIBED · UNVERIFIED' 316 383 238 '#FFF2D9' '#95620E'
                DrawTable $graphics 316 435 717 @('Measure','Count','Value basis','Review') @(
                    ,@('Quotation','Optional','Raw / net','Unverified'),
                    ,@('OC proxy','Optional','Normalized','Unverified'),
                    ,@('Delivery challan','Optional','Net','Review pending')
                ) 48
                FillRect $graphics 1079 270 485 397 '#FFFFFF'; StrokeRect $graphics 1079 270 485 397 $line
                DrawText $graphics 'Normalized value adjustment' 1103 290 440 31 18 $ink $true
                DrawText $graphics 'Document count stays unchanged.' 1103 343 416 30 15 $muted $false
                DrawText $graphics 'A second authorized user must approve.' 1103 392 416 50 15 $ink $true
                DrawPill $graphics 'SEPARATE APPROVER' 1103 465 205 '#EEF3FF' '#2F5FC4'
                DrawText $graphics 'Discrepancies are recorded for investigation; reconciliation does not rewrite the report.' 1103 526 420 78 14 $muted $false
                DrawPill $graphics 'NO IMPORT OR ADJUSTMENT SUBMITTED' 1103 615 344 '#E2F5F0' '#147D6C'
            }
            'freeze' {
                DrawSectionHeading $graphics 'Saved report run' 'Freeze after reviewing the scope, dates and source coverage.' 292 202 1200
                FillRect $graphics 292 270 818 404 '#FFFFFF'; StrokeRect $graphics 292 270 818 404 $line
                DrawText $graphics 'Snapshot contents' 316 292 720 34 19 $ink $true
                $snapshotRows = @(
                    'Selected period and comparison period',
                    'Authorized company or team scope',
                    'Historical cutoff and calendar version',
                    'Supporting ERP source facts and definitions',
                    'Run status and audit history'
                )
                $fy = 355
                foreach ($label in $snapshotRows) {
                    FillRect $graphics 316 ($fy + 5) 20 20 '#E2F5F0'
                    DrawText $graphics '✓' 318 ($fy + 3) 17 20 14 '#147D6C' $true
                    DrawText $graphics $label 351 $fy 700 30 15 $ink $false
                    $fy += 50
                }
                DrawPill $graphics 'RUN KEY HIDDEN IN THIS TOUR' 316 616 260 '#EEF3FF' '#2F5FC4'
                FillRect $graphics 1132 270 432 404 '#FFFFFF'; StrokeRect $graphics 1132 270 432 404 $line
                DrawText $graphics 'Export this saved run' 1156 292 384 34 19 $ink $true
                DrawText $graphics 'One snapshot keeps all export formats aligned.' 1156 340 370 44 14 $muted $false
                DrawPill $graphics 'CSV' 1156 419 89 '#EEF3FF' '#2F5FC4'
                DrawPill $graphics 'EXCEL' 1253 419 104 '#E2F5F0' '#147D6C'
                DrawPill $graphics 'PDF' 1365 419 84 '#FFF2D9' '#95620E'
                FillRect $graphics 1156 488 360 1 $line
                DrawText $graphics 'Access is checked again when the run is loaded or exported.' 1156 512 368 71 14 $ink $false
                DrawPill $graphics 'AUDITABLE SNAPSHOT' 1156 608 215 '#E2F5F0' '#147D6C'
            }
            'close' {
                DrawPill $graphics 'CONNECTED TO THE ERP DATABASE' 292 213 306 '#E2F5F0' '#147D6C'
                DrawText $graphics 'A reliable reporting path, with review still in the loop.' 292 275 1220 52 30 $ink $true
                DrawText $graphics 'Saved event examples stay separate from the core ERP case and transaction records.' 292 335 1190 38 17 $muted $false
                $closeCards = @(
                    @{Y=415;Title='DATABASE';State='Connected and populated';Color=$teal},
                    @{Y=492;Title='DEMO SEED';State='Test case · each new entry tagged DEMO ONLY';Color=$blue},
                    @{Y=569;Title='REPORT SCOPE';State='Controlled by existing permissions';Color=$amber},
                    @{Y=646;Title='METRIC DEFINITIONS';State='Provisional until owners approve';Color=$red}
                )
                foreach ($item in $closeCards) {
                    FillRect $graphics 292 $item.Y 1272 57 '#FFFFFF'; StrokeRect $graphics 292 $item.Y 1272 57 $line
                    FillRect $graphics 292 $item.Y 5 57 $item.Color
                    DrawText $graphics $item.Title 318 ($item.Y + 4) 280 49 14 $muted $true
                    DrawText $graphics $item.State 605 ($item.Y + 4) 820 49 16 $ink $true
                }
            }
        }
        $framePath = Join-Path $framesDir ($scene.Id + '.png')
        $bitmap.Save($framePath, [System.Drawing.Imaging.ImageFormat]::Png)
    }
    finally {
        $graphics.Dispose()
        $bitmap.Dispose()
    }
}

$sceneDurations = @()
foreach ($scene in $scenes) {
    $audioPath = Join-Path $demo ($scene.Id + '.wav')
    $durationText = & $ffprobe -v error -show_entries format=duration -of default=nw=1:nk=1 $audioPath
    if (-not $durationText) { throw "Could not read narration duration for $($scene.Id)." }
    $duration = [double]::Parse(([string]$durationText).Trim(), [Globalization.CultureInfo]::InvariantCulture)
    if ($scene.Id -ne '10-close') { $duration += 0.65 }
    $sceneDurations += $duration
}

$filterParts = @()
$videoLabels = @()
for ($i = 0; $i -lt $scenes.Count; $i++) {
    $frames = [Math]::Max(1, [int][Math]::Round($sceneDurations[$i] * 25))
    $label = "v$i"
    $filterTemplate = '[{0}:v]scale=1760:990,zoompan=z=1.025:d={1}:x=iw/2-iw/zoom/2:y=ih/2-ih/zoom/2:s=1600x900:fps=25,setsar=1[{2}]'
    $filterParts += ($filterTemplate -f $i, $frames, $label)
    $videoLabels += "[$label]"
}
$filterParts += ($videoLabels -join '') + "concat=n=$($scenes.Count):v=1:a=0[vout]"
$filterParts += '[vout]subtitles=docs/demo/sales-performance-walkthrough.srt[vfinal]'
$filter = $filterParts -join ';'

$arguments = New-Object 'System.Collections.Generic.List[string]'
$arguments.Add('-y')
$arguments.Add('-loglevel')
$arguments.Add('warning')
foreach ($scene in $scenes) {
    $arguments.Add('-i')
    $arguments.Add((Join-Path $framesDir ($scene.Id + '.png')))
}
$arguments.Add('-i')
$arguments.Add($narration)
$arguments.Add('-filter_complex')
$arguments.Add($filter)
$arguments.Add('-map')
$arguments.Add('[vfinal]')
$arguments.Add('-map')
$arguments.Add("$($scenes.Count):a:0")
$arguments.Add('-c:v')
$arguments.Add('libx264')
$arguments.Add('-preset')
$arguments.Add('medium')
$arguments.Add('-crf')
$arguments.Add('22')
$arguments.Add('-pix_fmt')
$arguments.Add('yuv420p')
$arguments.Add('-r')
$arguments.Add('25')
$arguments.Add('-c:a')
$arguments.Add('aac')
$arguments.Add('-b:a')
$arguments.Add('96k')
$arguments.Add('-movflags')
$arguments.Add('+faststart')
$arguments.Add('-metadata')
$arguments.Add('title=Sales Performance Reporting - Synchronized Illustrative Walkthrough')
$arguments.Add('-metadata')
$arguments.Add('comment=Illustrative UI visuals; no live figures, customer details or document identifiers shown.')
$arguments.Add($outputVideo)

& $ffmpeg @($arguments)
if ($LASTEXITCODE -ne 0) { throw "FFmpeg failed with exit code $LASTEXITCODE." }

$videoDuration = & $ffprobe -v error -show_entries format=duration -of default=nw=1:nk=1 $outputVideo
$videoSize = (Get-Item -LiteralPath $outputVideo).Length
Write-Output ("Created {0} ({1:N3} seconds, {2:N1} MB)." -f $outputVideo, [double]::Parse(([string]$videoDuration).Trim(), [Globalization.CultureInfo]::InvariantCulture), ($videoSize / 1MB))

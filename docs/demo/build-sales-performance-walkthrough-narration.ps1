$ErrorActionPreference = 'Stop'

Add-Type -AssemblyName System.Speech
$demo = $PSScriptRoot
$scenesFile = Join-Path $demo 'sales-performance-walkthrough.json'
$markdownFile = Join-Path $demo 'SALES_PERFORMANCE_WALKTHROUGH.md'
$captionsFile = Join-Path $demo 'sales-performance-walkthrough.srt'
$narrationFile = Join-Path $demo 'sales-performance-walkthrough-narration.wav'
$work = Join-Path $demo '.narration-build'
$ffmpeg = (Get-Command ffmpeg -ErrorAction Stop).Source
$ffprobe = (Get-Command ffprobe -ErrorAction Stop).Source
$voiceName = 'Microsoft Zira Desktop'
$sentenceGap = 0.18
$sceneGap = 0.65

function Get-AudioDuration([string]$path) {
    $value = & $ffprobe -v error -show_entries format=duration -of default=nw=1:nk=1 $path
    if ($LASTEXITCODE -ne 0 -or -not $value) { throw "Could not read audio duration: $path" }
    return [double]::Parse(([string]$value).Trim(), [Globalization.CultureInfo]::InvariantCulture)
}

function Format-Timecode([double]$seconds, [string]$separator) {
    $totalMs = [int64][Math]::Round($seconds * 1000, 0, [MidpointRounding]::AwayFromZero)
    $hours = [int][Math]::Floor($totalMs / 3600000)
    $minutes = [int][Math]::Floor(($totalMs % 3600000) / 60000)
    $wholeSeconds = [int][Math]::Floor(($totalMs % 60000) / 1000)
    $milliseconds = [int]($totalMs % 1000)
    return ('{0:00}:{1:00}:{2:00}{3}{4:000}' -f $hours, $minutes, $wholeSeconds, $separator, $milliseconds)
}

function Write-ConcatList([string]$path, [string[]]$files) {
    $lines = foreach ($file in $files) {
        $full = [IO.Path]::GetFullPath($file).Replace('\', '/')
        if ($full.Contains("'")) { throw "Concat path contains an unsupported quote: $full" }
        "file '$full'"
    }
    [IO.File]::WriteAllLines($path, $lines, [Text.Encoding]::ASCII)
}

$spec = Get-Content -LiteralPath $scenesFile -Raw | ConvertFrom-Json
if (-not $spec.scenes -or $spec.scenes.Count -ne 10) { throw 'The walkthrough must define ten scenes.' }
New-Item -ItemType Directory -Force -Path $work | Out-Null

$allSceneWavs = New-Object 'System.Collections.Generic.List[string]'
$subtitleLines = New-Object 'System.Collections.Generic.List[string]'
$sceneRanges = New-Object 'System.Collections.Generic.List[object]'
$timeline = 0.0
$subtitleIndex = 0
$sentenceNumber = 0
$sampleRate = 0
$channels = 0
$silenceFile = Join-Path $work 'silence.wav'

for ($sceneIndex = 0; $sceneIndex -lt $spec.scenes.Count; $sceneIndex++) {
    $scene = $spec.scenes[$sceneIndex]
    $sceneStart = $timeline
    $sentences = @([regex]::Split(([string]$scene.narration).Trim(), '(?<=[.!?])\s+(?=\p{Lu})') | Where-Object { $_.Trim() })
    if ($sentences.Count -eq 0) { throw "Scene has no narration: $($scene.id)" }
    $sceneParts = New-Object 'System.Collections.Generic.List[string]'

    for ($sentenceIndex = 0; $sentenceIndex -lt $sentences.Count; $sentenceIndex++) {
        $sentence = ([string]$sentences[$sentenceIndex]).Trim()
        $sentenceNumber++
        $sentenceFile = Join-Path $work ('sentence-{0:D3}.wav' -f $sentenceNumber)
        $synth = New-Object System.Speech.Synthesis.SpeechSynthesizer
        try {
            $synth.SelectVoice($voiceName)
            $synth.Rate = 0
            $synth.Volume = 100
            $synth.SetOutputToWaveFile($sentenceFile)
            $synth.Speak($sentence)
        }
        finally {
            $synth.Dispose()
        }

        if (-not (Test-Path -LiteralPath $sentenceFile)) { throw "Speech synthesis produced no file for scene $($scene.id)." }
        if ($sampleRate -eq 0) {
            $streamInfo = & $ffprobe -v error -select_streams a:0 -show_entries stream=sample_rate,channels -of json $sentenceFile | ConvertFrom-Json
            if ($LASTEXITCODE -ne 0 -or -not $streamInfo.streams.Count) { throw 'Could not inspect synthesized audio format.' }
            $sampleRate = [int]$streamInfo.streams[0].sample_rate
            $channels = [int]$streamInfo.streams[0].channels
            $channelLayout = if ($channels -eq 1) { 'mono' } else { 'stereo' }
            & $ffmpeg -y -loglevel error -f lavfi -i "anullsrc=r=$sampleRate`:cl=$channelLayout" -t $sentenceGap -c:a pcm_s16le -ar $sampleRate -ac $channels $silenceFile
            if ($LASTEXITCODE -ne 0) { throw 'Could not create sentence pause audio.' }
        }

        $duration = Get-AudioDuration $sentenceFile
        $cueStart = $timeline
        $cueEnd = $cueStart + $duration
        $subtitleIndex++
        $subtitleLines.Add([string]$subtitleIndex)
        $subtitleLines.Add("$(Format-Timecode $cueStart ',') --> $(Format-Timecode $cueEnd ',')")
        $subtitleLines.Add($sentence)
        $subtitleLines.Add('')
        $sceneParts.Add($sentenceFile)
        $timeline += $duration

        if ($sentenceIndex -lt ($sentences.Count - 1)) {
            $sceneParts.Add($silenceFile)
            $timeline += $sentenceGap
        }
    }

    $sceneWav = Join-Path $demo ($scene.id + '.wav')
    $sceneList = Join-Path $work ($scene.id + '.concat.txt')
    Write-ConcatList $sceneList $sceneParts.ToArray()
    & $ffmpeg -y -loglevel error -f concat -safe 0 -i $sceneList -c:a pcm_s16le -ar $sampleRate -ac $channels $sceneWav
    if ($LASTEXITCODE -ne 0) { throw "Could not assemble scene narration: $($scene.id)" }
    $allSceneWavs.Add($sceneWav)

    $sceneEnd = $timeline
    $sceneRanges.Add([pscustomobject]@{ Title = [string]$scene.title; Start = $sceneStart; End = $sceneEnd })
    if ($sceneIndex -lt ($spec.scenes.Count - 1)) {
        $allSceneWavs.Add($silenceFile)
        $timeline += $sceneGap
    }
}

$fullList = Join-Path $work 'walkthrough.concat.txt'
Write-ConcatList $fullList $allSceneWavs.ToArray()
& $ffmpeg -y -loglevel error -f concat -safe 0 -i $fullList -c:a pcm_s16le -ar $sampleRate -ac $channels $narrationFile
if ($LASTEXITCODE -ne 0) { throw 'Could not assemble the full narration.' }
[IO.File]::WriteAllText($captionsFile, ($subtitleLines -join "`n"), (New-Object Text.UTF8Encoding($false)))

$markdown = [IO.File]::ReadAllText($markdownFile)
foreach ($range in $sceneRanges) {
    $startText = Format-Timecode $range.Start '.'
    $endText = Format-Timecode $range.End '.'
    $titlePattern = [regex]::Escape($range.Title)
    $pattern = '(?m)^##[^\r\n]*' + $titlePattern + '[^\r\n]*'
    $replacement = '## {0}{3}{1} {4} {2}' -f $startText, $endText, $range.Title, [string][char]0x2013, [string][char]0x00B7
    $markdown = [regex]::Replace($markdown, $pattern, $replacement)
}
$runtime = Format-Timecode $timeline '.'
$markdown = [regex]::Replace($markdown, '(?m)^\*\*Runtime:\*\*.*$', "**Runtime:** $runtime (including 0.65s scene pauses)")
[IO.File]::WriteAllText($markdownFile, $markdown, (New-Object Text.UTF8Encoding($false)))

$workFull = [IO.Path]::GetFullPath($work)
$demoFull = [IO.Path]::GetFullPath($demo).TrimEnd([IO.Path]::DirectorySeparatorChar, [IO.Path]::AltDirectorySeparatorChar) + [IO.Path]::DirectorySeparatorChar
if (-not $workFull.StartsWith($demoFull, [StringComparison]::OrdinalIgnoreCase)) { throw "Refusing to remove temporary output outside the demo folder: $workFull" }
Remove-Item -LiteralPath $workFull -Recurse -Force

Write-Output ("Generated synchronized narration and captions: {0} seconds, {1} captions, {2}." -f $timeline, $subtitleIndex, $voiceName)

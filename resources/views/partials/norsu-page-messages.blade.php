{{-- Serialized flash + validation; drained by js/norsu-dtr-dialogs.js as centered prompts (Student / Coordinator / Admin layouts). --}}
@php
    $norsuPageMsgs = [];
    /** @var \Illuminate\Support\ViewErrorBag|null $errors */
    $errs = isset($errors) && $errors instanceof \Illuminate\Support\ViewErrorBag ? $errors : null;

    $norsuPushMsg = static function (
        array &$bucket,
        string $title,
        $message,
        string $variant = 'neutral',
        string $confirmText = 'OK'
    ): void {
        $message = is_string($message) ? trim(strip_tags($message)) : '';
        if ($message === '') {
            return;
        }
        $bucket[] = [
            'title' => $title !== '' ? $title : '',
            'message' => $message,
            'variant' => $variant,
            'confirmText' => $confirmText,
        ];
    };

    $hasValErr = $errs && method_exists($errs, 'any') && $errs->any();
    if ($hasValErr) {
        $lines = $errs->all();
        $lines = collect($lines)->map(fn ($l) => is_string($l) ? strip_tags(trim($l)) : '')->unique()->filter()->values();
        if ($lines->isNotEmpty()) {
            $body = $lines->map(fn ($line) => '• '.$line)->implode("\n");
            $norsuPushMsg($norsuPageMsgs, 'Check your entry', $body, 'danger');
        }
    }

    $msgOrder = [
        ['key' => 'error', 'title' => 'Something went wrong', 'variant' => 'danger'],
        ['key' => 'warning', 'title' => 'Please note', 'variant' => 'warning'],
        ['key' => 'info', 'title' => 'Notice', 'variant' => 'info'],
        ['key' => 'success', 'title' => 'Success', 'variant' => 'success'],
        ['key' => 'status', 'title' => 'Updated', 'variant' => 'success'],
    ];
    foreach ($msgOrder as $def) {
        $k = $def['key'];
        $content = session($k);
        if (! is_string($content) || trim($content) === '') {
            continue;
        }
        $title = $def['title'];
        $variant = $def['variant'];
        $text = strip_tags(trim($content));
        if ($k === 'error' && session('error_type')) {
            $title = 'Verification failed';
        }
        if ($hasValErr && in_array($k, ['success', 'status'], true)) {
            continue;
        }
        $norsuPushMsg($norsuPageMsgs, $title, $text, $variant);
    }
@endphp
@if(count($norsuPageMsgs) > 0)
<noscript>
    <div class="norsu-page-msgs-noscript" role="alert" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);max-width:420px;margin:12px;background:#fef2f2;color:#450a0a;border:1px solid #fecaca;padding:1rem 1.1rem;border-radius:12px;z-index:99999;line-height:1.45;font-size:14px;">
        @foreach($norsuPageMsgs as $_m)<p style="margin:0 0 10px;"><strong>{{ $_m['title'] }}</strong><br>{{ $_m['message'] }}</p>@endforeach
        <small style="color:#7f1d1d;">Enable JavaScript for the full confirmation dialog.</small>
    </div>
</noscript>
<script type="application/json" id="norsu-page-msgs">{!! json_encode($norsuPageMsgs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}</script>
@endif

@include('layouts.app', [
    'slot' => $slot,
    'title' => $title ?? null,
    'headerTitle' => $headerTitle ?? null,
    'breadcrumbs' => $breadcrumbs ?? null,
])

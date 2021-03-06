<!DOCTYPE html>
<html lang="{!! __('lang_identifier') !!}" dir="{!! __('lang_direction') !!}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{!! __('ForkBB Installation') !!}</title>
@foreach ($p->pageHeaders as $pageHeader)
    @if ('style' === $pageHeader['type'])
  <style>{!! $pageHeader['values'][0] !!}</style>
    @else
  <{!! $pageHeader['type'] !!} @foreach ($pageHeader['values'] as $key => $val) {!! $key !!}="{{ $val }}" @endforeach>
    @endif
@endforeach
</head>
<body>
  <div id="fork">
    <header id="fork-header">
      <div id="id-fhtitle">
        <h1 id="id-fhth1">{!! __('ForkBB Installation') !!}</h1>
        <p id="id-fhtdesc">{!! __('Welcome') !!}</p>
      </div>
    </header>
@if ($iswev = $p->fIswev)
    @include ('layouts/iswev')
@endif
@if ($form = $p->form1)
    <section class="f-install">
      <h2>{!! __('Choose install language') !!}</h2>
      <div class="f-fdiv">
    @include ('layouts/form')
      </div>
    </section>
@endif
@if (! $p->fIswev['e'])
    @if ($form = $p->form2)
    <section class="f-install">
      <h2>{!! __('Install', $p->rev) !!}</h2>
      <div class="f-fdiv">
        @include ('layouts/form')
      </div>
    </section>
    @endif
@endif
<!-- debuginfo -->
  </div>
</body>
</html>

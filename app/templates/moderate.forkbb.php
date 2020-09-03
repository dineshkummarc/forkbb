@include ('layouts/crumbs')
@extends ('layouts/main')
    <div class="f-nav-links">
@yield ('crumbs')
    </div>
@if ($form = $p->form)
    <section class="f-moderate-form f-main">
      <h2>{!! $p->formTitle !!}</h2>
      <div class="f-fdiv">
    @include ('layouts/form')
      </div>
    </section>
@endif

@if ($iswev['i'])
    <section class="f-iswev f-info">
      <h2>Info message</h2>
      <ul>
    @foreach ($iswev['i'] as $cur)
        <li class="f-icontent">{!! $cur !!}</li>
    @endforeach
      </ul>
    </section>
@endif
@if ($iswev['s'])
    <section class="f-iswev f-success">
      <h2>Successful operation message</h2>
      <ul>
    @foreach ($iswev['s'] as $cur)
        <li class="f-scontent">{!! $cur !!}</li>
    @endforeach
      </ul>
    </section>
@endif
@if ($iswev['w'])
    <section class="f-iswev f-warning">
      <h2>Warning message</h2>
      <ul>
    @foreach ($iswev['w'] as $cur)
        <li class="f-wcontent">{!! $cur !!}</li>
    @endforeach
      </ul>
    </section>
@endif
@if ($iswev['e'])
    <section class="f-iswev f-error">
      <h2>Error message</h2>
      <ul>
    @foreach ($iswev['e'] as $cur)
        <li class="f-econtent">{!! $cur !!}</li>
    @endforeach
      </ul>
    </section>
@endif
@if ($iswev['v'])
    <section class="f-iswev f-validation">
      <h2>Validation message</h2>
      <ul>
    @foreach ($iswev['v'] as $cur)
        <li class="f-vcontent">{!! $cur !!}</li>
    @endforeach
      </ul>
    </section>
@endif

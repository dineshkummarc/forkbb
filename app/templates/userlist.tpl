@section ('crumbs')
      <ul class="f-crumbs">
  @foreach ($p->crumbs as $cur)
        <li class="f-crumb"><!-- inline -->
    @if ($cur[0])
          <a href="{!! $cur[0] !!}" @if ($cur[2]) class="active" @endif>{{ $cur[1] }}</a>
    @else
          <span @if ($cur[2]) class="active" @endif>{{ $cur[1] }}</span>
    @endif
        </li><!-- endinline -->
  @endforeach
      </ul>
@endsection
@section ('pagination')
  @if ($p->pagination)
        <nav class="f-pages">
    @foreach ($p->pagination as $cur)
      @if ($cur[2])
          <a class="f-page active" href="{!! $cur[0] !!}">{{ $cur[1] }}</a>
      @elseif ('info' === $cur[1])
          <span class="f-pinfo">{!! $cur[0] !!}</span>
      @elseif ('space' === $cur[1])
          <span class="f-page f-pspacer">{!! __('Spacer') !!}</span>
      @elseif ('prev' === $cur[1])
          <a rel="prev" class="f-page f-pprev" href="{!! $cur[0] !!}">{!! __('Previous') !!}</a>
      @elseif ('next' === $cur[1])
          <a rel="next" class="f-page f-pnext" href="{!! $cur[0] !!}">{!! __('Next') !!}</a>
      @else
          <a class="f-page" href="{!! $cur[0] !!}">{{ $cur[1] }}</a>
      @endif
    @endforeach
        </nav>
  @endif
@endsection
@extends ('layouts/main')
    <div class="f-nav-links">
@yield ('crumbs')
@if ($p->pagination)
      <div class="f-links-b clearfix">
  @yield ('pagination')
      </div>
@endif
    </div>
@if ($form = $p->form)
    <section class="f-main f-userlist-form">
      <h2>{!! __('Search') !!}</h2>
      <div class="f-fdiv">
  @include ('layouts/form')
      </div>
    </section>
@endif
@if ($p->userList)
    <section class="f-main f-userlist">
      <h2>{!! __('User list') !!}</h2>
      <div class="f-ulist">
        <ol class="f-table">
          <li class="f-row f-thead" value="{{ $p->startNum }}">
            <span class="f-hcell f-cusername">
              <span class="f-hc-table">
                <span class="f-hc-tasc">▲</span>
                <span class="f-hc-tname">{!! __('Username') !!}</span>
                <span class="f-hc-tdesc">▼</span>
              </span>
            </span>
            <span class="f-hcell f-ctitle">{!! __('Title') !!}</span>
    @if ($p->user->showPostCount)
            <span class="f-hcell f-cnumposts">
              <span class="f-hc-table">
                <span class="f-hc-tasc">▲</span>
                <span class="f-hc-tname">{!! __('Posts') !!}</span>
                <span class="f-hc-tdesc">▼</span>
              </span>
            </span>
    @endif
            <span class="f-hcell f-cdatereg">
              <span class="f-hc-table">
                <span class="f-hc-tasc">▲</span>
                <span class="f-hc-tname">{!! __('Registered') !!}</span>
                <span class="f-hc-tdesc">▼</span>
              </span>
            </span>
          </li>
  @foreach ($p->userList as $user)
          <li class="f-row">
    @if ($p->user->viewUsers && $user->link)
            <span class="f-cell f-cusername"><a href="{!! $user->link !!}">{{ $user->username }}</a></span>
    @else
            <span class="f-cell f-cusername">{{ $user->username }}</span>
    @endif
            <span class="f-cell f-ctitle"><span>(</span><i>{{ $user->title() }}</i><span>),</span></span>
    @if ($p->user->showPostCount)
            <span class="f-cell f-cnumposts">{!! __('<b>%s</b><span> post,</span>', $user->num_posts, num($user->num_posts)) !!}</span>
    @endif
            <span class="f-cell f-cdatereg">{!! __('<span>registered: </span><b>%s</b>', dt($user->registered, true)) !!}</span>
          </li>
  @endforeach
        </ol>
      </div>
    </section>
  @if ($p->pagination)
    <div class="f-nav-links">
      <div class="f-links clearfix">
    @yield ('pagination')
      </div>
    </div>
  @endif
@endif
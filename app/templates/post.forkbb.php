@include ('layouts/crumbs')
@extends ('layouts/main')
    <div class="f-nav-links">
@yield ('crumbs')
    </div>
@if ($p->previewHtml)
    <section class="f-main f-preview">
      <h2>{!! __('Post preview') !!}</h2>
      <div class="f-post-body">
        <div class="f-post-main">
          {!! $p->previewHtml !!}
        </div>
      </div>
    </section>
@endif
@if ($form = $p->form)
    <section class="f-post-form">
      <h2>{!! $p->formTitle !!}</h2>
      <div class="f-fdiv">
    @include ('layouts/form')
      </div>
    </section>
@endif
@if ($p->posts)
    <section class="f-view-posts">
      <h2>{!! $p->postsTitle !!}</h2>
    @foreach ($p->posts as $post)
        @if ($post->id)
      <article id="p{!! $post->id !!}" class="f-post">
        <header class="f-post-header">
          <span class="f-post-posted"><time datetime="{{ \gmdate('c', $post->posted) }}">{{ dt($post->posted) }}</time></span>
          <span class="f-post-number"><a href="{!! $post->link !!}" rel="bookmark">#{!! $post->postNumber !!}</a></span>
        </header>
        <address class="f-post-user">
          <ul class="f-user-info-first">
            <li class="f-username">{{ $post->poster }}</li>
          </ul>
          <ul class="f-user-info">
            <li class="f-username">{{ $post->poster }}</li>
          </ul>
        </address>
        <div class="f-post-body">
          <div class="f-post-main">
            {!! $post->html() !!}
          </div>
        </div>
      </article>
        @endif
    @endforeach
    </section>
@endif

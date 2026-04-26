<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
    <title>{{ $siteBrand['site_name'] }} — Actualités</title>
    <link>{{ url('/') }}</link>
    <description>Le magazine culturel et touristique de Côte d'Ivoire.</description>
    <language>fr</language>
    <lastBuildDate>{{ now()->toRfc822String() }}</lastBuildDate>
    <atom:link href="{{ route('rss') }}" rel="self" type="application/rss+xml"/>
    <image>
        @if(!empty($siteBrand['logo_url']))
        <url>{{ $siteBrand['logo_url'] }}</url>
        @endif
        <title>{{ $siteBrand['site_name'] }}</title>
        <link>{{ url('/') }}</link>
    </image>

    @foreach($articles as $article)
    <item>
        <title><![CDATA[{{ $article->title_fr }}]]></title>
        <link>{{ route('articles.show', $article->slug_fr) }}</link>
        <guid isPermaLink="true">{{ route('articles.show', $article->slug_fr) }}</guid>
        <pubDate>{{ $article->published_at->toRfc822String() }}</pubDate>
        <author>{{ $article->author?->email ?? config('mail.from.address') }} ({{ $article->author?->full_name ?? $siteBrand['site_name'] }})</author>
        @if($article->category)
        <category><![CDATA[{{ $article->category->name_fr }}]]></category>
        @endif
        @if($article->excerpt_fr)
        <description><![CDATA[{{ $article->excerpt_fr }}]]></description>
        @endif
        @if($article->cover_url)
        <enclosure url="{{ $article->cover_url }}" type="image/jpeg" length="0"/>
        @endif
    </item>
    @endforeach

</channel>
</rss>

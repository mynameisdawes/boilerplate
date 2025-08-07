@php
use Vektor\Utilities\Utilities;
$posts_attrs_attrs = [];
if (isset($cms_entity) && isset($cms_type) && $cms_type == 'post_category') {
    $posts_attrs_attrs['category_ids'] = [ $cms_entity->id ];
}
if (isset($cms_entity) && isset($cms_type) && $cms_type == 'post_tag') {
    $posts_attrs_attrs['tag_ids'] = [ $cms_entity->id ];
}
$posts_attrs_props = '';
if (!empty($posts_attrs_attrs)) {
    $posts_attrs_props = ' ' . Utilities::arrayToVueAttributes($posts_attrs_attrs);
}
@endphp
<c-cms_posts{!! $posts_attrs_props !!}>
    <template v-slot:default="postsScope">
        <div class="container-gutter:outer">
            @if (isset($cms_entity) && isset($cms_type) && in_array($cms_type, [
                'post_category',
                'post_tag',
            ]))
                <div class="document__header__actions">
                    <div class="document__navigation_strip">
                        <div class="container:xl">
                            <div class="content__wrapper">
                                <div class="content">
                                    <ul class="breadcrumbs">
                                        <li><a href="https://boilerplate.dev/blog">Back to Blog</a></li>
                                        @if (isset($cms_entity) && isset($cms_type) && $cms_type == 'post_category')
                                            @php
                                                $cms_entity_ancestors = $cms_entity->ancestors();
                                            @endphp
                                            @if ($cms_entity_ancestors->count() > 0)
                                                @foreach ($cms_entity_ancestors as $cms_entity_ancestor)
                                                    <li><a href="{{ $cms_entity_ancestor->href }}">{{ $cms_entity_ancestor->name }}</a></li>
                                                @endforeach
                                            @endif
                                        @endif
                                        <li>{{ $cms_entity->name }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="spinner__wrapper" :class="{ is_loading: postsScope.is_loading == true }">
                <div class="spinner"></div>
            </div>
            <div class="container:xl">
                @if (isset($cms_entity) && isset($cms_type) && $cms_type == 'post_category')
                    <h1>Blog posts in the {{ $cms_entity->name }} category</h1>
                @elseif (isset($cms_entity) && isset($cms_type) && $cms_type == 'post_tag')
                    <h1>Blog posts with the {{ $cms_entity->name }} tag</h1>
                @else
                    <h1>Blog posts</h1>
                @endif
                <ul class="cms_entity_cards blog_entity_cards" v-if="postsScope.posts.length > 0">
                    <li v-for="post in postsScope.posts" :key="post.id" class="cms_entity_card">
                        <a :href="post.href" tabindex="-1"></a>
                        <div class="card_content">
                            <div class="visual" v-if="post.formatted_meta_image">
                                <img :src="post.formatted_meta_image" :alt="post.title">
                            </div>
                            <div class="textual">
                                <div class="article">
                                    <header class="article_header" :style="{ 'view-transition-name': 'article_header_' + post.id }">
                                        <div class="h2">@{{ post.title }}</div>
                                        <div class="collection metadata">
                                            <div v-if="post.author">@{{ post.author }}</div>
                                            <div v-if="post.formatted_publish_date" v-html="post.formatted_publish_date"></div>
                                        </div>
                                    </header>
                                    <p>@{{ post.excerpt }}</p>
                                </div>
                                <div class="collection">
                                    <button class="btn bg-primary border-primary text-primary_contrasting">Read post</button>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <c-message v-if="postsScope.posts_fetched == true && postsScope.posts.length == 0" required="true" content="There are no posts yet" :trigger="true"></c-message>
                <c-pagination v-show="postsScope.posts_fetched == true && postsScope.posts.length > 0 && postsScope.paginate === true" :properties="postsScope.pagination" :per_pages="postsScope.per_pages" @change-pagination="postsScope.getPosts"></c-pagination>
            </div>
        </div>
    </template>
</c-cms_posts>


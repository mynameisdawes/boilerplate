@extends('layouts.default')
@php
    $default_title = 'Article';
@endphp
@if (isset($page))
    @section('title', $page->title ? $page->title : $default_title)
    @section('meta_title', !empty($page->meta_title) ? $page->meta_title : $page->title)
    @section('meta_description', $page->meta_description)
    @section('meta_image', $page->meta_image)
@else
    @section('title', $default_title)
@endif

@section('content')
    <div class="container-gutter:outer">
        <div class="container:xl">
            <article class="article article--overflow container:xl:1t2e edgeless:1t2e">
                <h1 class="text-gradient">Article</h1>
                <div class="collection metadata">
                    <div>Article Author</div>
                    <div>21<sup>st</sup> November 2024</div>
                </div>
                <p>As an agency, we are constantly striving to stay ahead of the curve and provide our clients with the latest and most effective marketing strategies. In today's digital age, it's more important than ever to have a strong online presence, and that's why we specialize in digital marketing. From social media management to SEO and PPC advertising, we have the expertise to help you reach your target audience and achieve your business goals.</p>
                <p>One of the key components of any successful digital marketing campaign is content. Quality content is not only important for attracting and engaging your audience, but it also plays a crucial role in SEO. At our agency, we have a team of talented writers and content creators who can help you develop a content strategy that drives results. Whether you need blog posts, social media content, or email newsletters, we can create compelling and effective content that resonates with your audience.</p>
                <picture>
                    <source srcset="{{ url('assets/img/article__image.webp') }}" type="image/webp" width="650" height="433" />
                    <source srcset="{{ url('assets/img/article__image.jpg') }}" type="image/jpeg" width="650" height="433" />
                    <img src="{{ url('assets/img/article__image.jpg') }}" alt="Bird's-eye view island" width="650" height="433" class="alignleft edgeless:1t2e w-full:1t2e" />
                </picture>
                <p>Another important aspect of digital marketing is social media. Social media platforms offer businesses the opportunity to connect with their customers on a more personal level, and can be a powerful tool for building brand awareness and driving sales. Our team can help you develop a social media strategy that is tailored to your business goals and target audience, and create engaging content that encourages interaction and builds brand loyalty.</p>
                <ul>
                    <li>Customised web solutions: Our agency provides customized web solutions that are tailored to meet the specific needs of each of our clients.</li>
                    <li>Our data-driven approach ensures that our clients get a website that is not only visually appealing but also highly effective at achieving their business objectives.</li>
                </ul>
                <p>In addition to social media, SEO is another critical component of digital marketing. Without a solid SEO strategy, your website may not appear in search engine results when potential customers are searching for your products or services. Our team can help you optimize your website for search engines, identify relevant keywords, and create content that ranks well in search results. We also stay up-to-date on the latest SEO trends and best practices, so you can be sure your strategy is always in line with industry standards.</p>
                <p>PPC advertising is another effective way to reach your target audience online. With PPC ads, you only pay when someone clicks on your ad, making it a cost-effective way to drive traffic to your website and generate leads. Our team can help you set up and manage a PPC campaign that targets the right audience and delivers results. We also provide detailed reporting and analysis to help you measure the success of your campaign and make adjustments as needed.</p>
                <blockquote>
                    <p>I loved it! It's been the best part of getting to know you!</p>
                    <footer>
                        <cite>David</cite>
                    </footer>
                </blockquote>
                <p>At our agency, we are committed to helping our clients achieve their business goals through effective digital marketing strategies. Whether you're just starting out or looking to take your existing campaigns to the next level, we have the expertise and experience to help you succeed. Contact us today to learn more about our services and how we can help you grow your business online.</p>
            </article>
        </div>
    </div>
@endsection

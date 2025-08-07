@extends('layouts.default')
@php
    $default_title = 'Privacy Policy';
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
        <div class="container:md">
            <article class="article article--overflow container:xl:1t2e edgeless:1t2e">
                <h1 class="text-gradient">Privacy Policy</h1>
                <p>At <strong>vektor</strong>, we understand the importance of your privacy and are committed to protecting your personal data. This Privacy Policy explains how we collect, use, and share your information when you visit our website or use our services.</p>
                <p>By using our website or services, you agree to the terms of this Privacy Policy. If you do not agree, please refrain from using our website.</p>

                <h2 class="h3">1. Definitions</h2>
                <ul class="pl-6 list-outside list-decimal">
                    <li><strong>Account:</strong> Refers to an account required to access certain areas and features of our website.</li>
                    <li><strong>Cookie:</strong> Refers to a small text file placed on your computer or device when you use our website. Details about the cookies we use can be found in section 13 below.</li>
                    <li><strong>Cookie Law:</strong> Refers to the relevant parts of the Privacy and Electronic Communications (EC Directive) Regulations 2003.</li>
                    <li><strong>Personal data:</strong> Refers to any information that can directly or indirectly identify an individual. In this context, it refers to the data you provide to us through our website.</li>
                    <li><strong>We/Us/Our:</strong> Refers to vektor.</li>
                </ul>

                <h2 class="h3">2. Information About Us</h2>
                <p>Our website, <a href="{{ route('base') }}">{{ route('base') }}</a>, is owned and operated by vektor.</p>

                <h2 class="h3">3. Scope of the Policy</h2>
                <p>This Privacy Policy applies solely to your use of our website. Please note that we do not have control over how other websites collect, store, or use your data. We recommend reviewing the privacy policies of those websites before providing any information.</p>

                <h2 class="h3">4. Your Rights</h2>
                <p>As a data subject, you have rights under applicable data protection laws, including:</p>
                <ul class="pl-6 list-outside list-decimal">
                    <li>The right to be informed about how we collect and use your personal data.</li>
                    <li>The right to access the personal data we hold about you.</li>
                    <li>The right to rectify any inaccurate or incomplete personal data we hold.</li>
                    <li>The right to request the deletion of your personal data.</li>
                    <li>The right to restrict the processing of your personal data.</li>
                    <li>The right to data portability.</li>
                    <li>The right to object to the use of your personal data for specific purposes.</li>
                    <li>The right to lodge a complaint with a supervisory authority.</li>
                </ul>
                <p>For more information or to exercise your rights, please contact us using the details provided in section 14 or refer to the relevant supervisory authority.</p>

                <h2 class="h3">5. Data We Collect</h2>
                <p>Depending on your use of our website, we may collect personal and non-personal data, including but not limited to:</p>
                <ul class="pl-6 list-outside list-decimal">
                    <li>Email Address</li>
                    <li>First Name</li>
                    <li>Last Name</li>
                    <li>Company</li>
                    <li>Address</li>
                    <li>Phone Number</li>
                    <li>IP Address</li>
                    <li>Browser Type</li>
                    <li>Operating System</li>
                    <li>Referring Website</li>
                    <li>Pages Visited</li>
                    <li>Date and Time of Visits</li>
                </ul>

                <h2 class="h3">6. How We Use Your Data</h2>
                <p>We use your personal data for the following purposes:</p>
                <ul class="pl-6 list-outside list-decimal">
                    <li>To provide and maintain our website and services.</li>
                    <li>To personalize your experience on our website.</li>
                    <li>To communicate with you, including responding to your inquiries and providing updates.</li>
                    <li>To send you marketing communications, if you have opted in to receive them.</li>
                    <li>To comply with legal obligations.</li>
                </ul>

                <h2 class="h3">7. How We Share Your Data</h2>
                <p>We may share your personal data with third parties in the following circumstances:</p>
                <ul class="pl-6 list-outside list-decimal">
                    <li>With your consent.</li>
                    <li>With service providers who help us operate our website and provide our services.</li>
                    <li>With our business partners and affiliates for marketing and promotional purposes.</li>
                    <li>If required by law or to protect our rights or the rights of others.</li>
                </ul>

                <h2 class="h3">8. Data Security</h2>
                <p>We take reasonable measures to protect your personal data from unauthorized access, use, or disclosure. However, please be aware that no method of transmission over the internet or electronic storage is 100% secure.</p>

                <h2 class="h3">9. International Data Transfers</h2>
                <p>If we transfer your data outside of your jurisdiction, we ensure appropriate safeguards are in place to protect your privacy rights.</p>

                <h2 class="h3">10. Children's Privacy</h2>
                <p>Our website is not intended for children under the age of 16. We do not knowingly collect personal data from children without parental consent. If you believe we have inadvertently collected data from a child, please contact us to have it removed.</p>

                <h2 class="h3">11. Cookies</h2>
                <p>We use cookies and similar technologies on our website. By using our website, you consent to the use of cookies as described in our Cookie Policy.</p>

                <h2 class="h3">12. Changes to this Policy</h2>
                <p>We may update this Privacy Policy from time to time. We will notify you of any significant changes and seek your consent if required by law.</p>

                <h2 class="h3">13. Contact Us</h2>
                <p>If you have any questions or concerns regarding this Privacy Policy or our data practices, please contact us at <a href="mailto:hello@vektor.co.uk">hello@vektor.co.uk</a>.</p>

                <p>Last updated: 17/05/2023</p>
            </article>
        </div>
    </div>
@endsection

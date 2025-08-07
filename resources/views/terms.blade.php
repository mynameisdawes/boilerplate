@extends('layouts.default')
@php
    $default_title = 'Terms & Conditions';
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
                <h1 class="text-gradient">Terms & Conditions</h1>
                <p>Welcome to the terms and conditions of the company. These terms and conditions govern the utilization of the services and website provided by the company. By accessing or using the company's services and website, you hereby acknowledge and agree to abide by these terms and conditions.</p>
                <ol>
                    <li><strong>These terms</strong>
                        <ol>
                            <li><strong>What these terms cover.</strong> These are the terms and conditions on which we supply products to you.</li>
                            <li><strong>Why you should read them.</strong> Please read these terms carefully before you submit your order to us. These terms tell you who we are, how we will provide products to you, how you and we may change or end the contract, what to do if there is a problem and other important information. If you think that there is a mistake in these terms, please contact us to discuss. These terms may be updated form time to time and may change depending on the time in which you place your order. We suggest you always check our website and these terms to ensure you understand them prior to each and every order you make.</li>
                            <li><strong>Are you a consumer or business customer?</strong> You are a consumer if:</li>
                            <ul>
                                <li>You are an individual.</li>
                                <li>You are buying products from us wholly or mainly for your personal use (not for use in connection with your trade, business, craft or profession). </li>
                            </ul>
                            <li><strong>If you are a business customer this is our entire agreement with you.</strong> If you are a business customer these terms constitute the entire agreement between us in relation to your purchase. You acknowledge that you have not relied on any statement, promise, representation, assurance or warranty made or given by or on behalf of us which is not set out in these terms and that you shall have no claim for innocent or negligent misrepresentation or negligent misstatement based on any statement in this Agreement. </li>
                        </ol>
                    </li>
                    <li><strong>About us</strong>
                        <ol>
                            <li><strong>Company details.</strong> Vektor UK Limited (company number 05783418) (<strong>we</strong> and <strong>us</strong>) is a company registered in England and Wales and our registered office is at Bolney Place, Cowfold Road, Bolney, Haywards Heath, RH17 5QT. Our VAT number is 865 0512 31. We operate the website vektor.co.uk.</li>
                            <li><strong>Contacting us.</strong> To contact us telephone our customer service team at <a href="tel:00441444657399">+44 1444 657 399</a> or email <a href="mailto:hello@vektor.co.uk">hello@vektor.co.uk</a>. How to give us formal notice of any matter under the Contract is set out in clause 23.2.</li>
                        </ol>
                    </li>
                    <li><strong>Our contract with you</strong>
                        <ol>
                            <li><strong>Our contract.</strong> These terms and conditions (<strong>Terms</strong>) apply to the order by you and supply of goods by us to you (<strong>Contract</strong>). No other terms are implied by trade, custom, practice or course of dealing.</li>
                            <li><strong>Language.</strong> These Terms and the Contract are made only in the English language.</li>
                        </ol>
                    </li>
                    <li><strong>Placing an order and its acceptance</strong>
                        <ol>
                            <li><strong>Placing your order.</strong> Please contact our customer sales team or follow the onscreen prompts to place an order. Each order is an offer by you to buy the goods specified in the order (<strong>Goods and/or Digital Services</strong>) and/or the Digital Services (<strong>Digital Services</strong>) specified in the order subject to these Terms.</li>
                            <li><strong>Correcting input errors.</strong> Our order process allows you to check and amend any errors before submitting your order to us. Please check the order carefully before confirming it. You are responsible for ensuring that your order and any specification submitted by you is complete and accurate.</li>
                            <li><strong>Acknowledging receipt of your order.</strong> After you place an order, you will receive an email from us acknowledging that we have received it, but please note that this does not mean that your order has been accepted. Our acceptance of your order will take place as described in clause 4.4.</li>
                            <li><strong>Accepting your order.</strong> Our acceptance of your order takes place when we send the email to you to accept it, at which point the Contract between you and us will come into existence.</li>
                            <li><strong>If we cannot accept your order.</strong> If we are unable to supply you with the Goods and/or Digital Services for any reason, we will inform you of this by email and we will not process your order. If you have already paid for the Goods and/or Digital Services, we will refund you the full amount including any delivery costs charged for Goods as soon as possible.</li>
                        </ol>
                    </li>
                    <li><strong>Our Goods</strong>
                        <ol>
                            <li>The images of the Goods on our site are for illustrative purposes only. Although we have made every effort to display the colours accurately, we cannot guarantee that your computer's display of the colours accurately reflect the colour of the Goods. The colour of your Goods may vary slightly from those images. A variation in colour or pattern or material between the delivered goods and the sample, image or description does not entitle you to reject the goods or to claim compensation.</li>
                            <li>Although we have made every effort to be as accurate as possible, because our Goods are handmade, all sizes, weights, capacities, dimensions and measurements indicated on our site may vary.</li>
                            <li>The packaging of your Goods may vary from that shown on images on our site.</li>
                        </ol>
                    </li>
                    <li><strong>Your rights to make changes</strong>
                        <ol>
                            <p>If you realise that you have made an error when placing your order or if you wish to make a change to the product you have ordered please contact us. We will let you know if the change is possible. If it is possible we will let you know about any changes to the price of the product, the timing of supply or anything else which would be necessary as a result of your requested change and ask you to confirm whether you wish to go ahead with the change. Once we have started the production, we will not allow any cancellation or changes to the order. If we cannot make the change or the consequences of making the change are unacceptable to you, you may want to end the contract (see clause 13 - Your rights to end the contract).</p>
                        </ol>
                    </li>
                    <li><strong>Our rights to make changes</strong>
                        <ol>
                            <li>Minor changes to the products. We may change the product:
                                <ol>
                                    <li>to reflect any changes in relevant laws and regulatory requirements; and</li>
                                    <li>to implement minor technical adjustments and improvements.</li>
                                </ol>
                            </li>
                            <li>Changes we can only make if we give you notice and an option to terminate. We can also make the following types of change to the product or these terms, but if we do so we'll notify you and you can then contact our Customer Service Team: https://vektor.co.uk/contact/ to end the contract before the change takes effect and receive a refund for any products you've paid for, but not received:
                                <ul>
                                    <li>Provide a substitute garment if we are out of stock of the Goods.</li>
                                </ul>
                            </li>
                            <li>Where we are providing bespoke goods customised by you, you acknowledge that there may be certain design constraints, including designs that may not be printable, may be offensive or infringe on any third party intellectual property rights. </li>
                            <li>We can stop providing a product, such as an ongoing service or a subscription for digital services or goods. We let you know at least 30 days in advance and we refund any sums you've paid in advance for products which won't be provided.</li>
                        </ol>
                    </li>
                    <li><strong>Return and refund</strong>
                        <ol>
                            <li>You may cancel the Contract and receive a refund, if you notify us as set out in clause 8.2.1 within 14 days of your receipt of our email accepting your order.</li>
                            <li>However, this cancellation right does not apply in the case of:
                                <ol>
                                    <li>Digital products or services once you have started to download or stream these or the services have been completed;</li>
                                    <li>products sealed for health protection or hygiene purposes, once these have been unsealed after you receive them;</li>
                                    <li>goods that are made to your specifications or are clearly personalised; and</li>
                                    <li>goods which become mixed inseparably with other items after their delivery.</li>
                                </ol>
                            </li>
                            <li>To cancel the Contract, you must contact our customer service team via our <a href="https://vektor.co.uk/contact">contact form</a>.If you use this method we will email you to confirm we have received your cancellation.<br>You can also email us at <a href="mailto:hello@vektor.co.uk">hello@vektor.co.uk</a> or contact our Customer Services team by telephone on <a href="tel:00441444657399">+44 1444 657 399</a>. If you are emailing us or writing to us please include details of your order to help us to identify it. If you send us your cancellation notice by email or by post, then your cancellation is effective from the date you send us the email or post the letter to us. For example, you will have given us notice in time as long as you get your letter into the last post on the last day of the cancellation period or email us before midnight on that day.</li>
                            <li>If you have returned the Goods to us under this clause 6 because they are faulty or mis-described, we will refund the price of the Goods and will refund you via bank transfer or on the credit card or debit card used by you depending on how you made payment.</li>
                            <li>If Goods have been delivered to you before you decide to cancel the Contract then you must return them to us without undue delay and in any event not later than 14 days after the day on which you let us know that you wish to cancel the Contract. You can either send them back, return them to us in-store or hand them to our authorised carrier. </li>
                        </ol>
                    </li>
                    <li><strong>Delivery, transfer of risk and title</strong>
                        <ol>
                            <li>We will contact you with an estimated delivery date, which we will email you when we confirm our acceptance of your order. You acknowledge that such a date is just an estimation and is subject to change. We will continue to update you on the progress of your order and any changes to the estimated delivery date.  Occasionally our delivery to you may be affected by an Event Outside Our Control. See clause 22 for our responsibilities when this happens.</li>
                            <li>Delivery is complete once the Goods have been unloaded at the address for delivery set out in your order and the Goods will be at your risk from that time.</li>
                            <li>You own the Goods once we have received payment in full, including of all applicable delivery charges.</li>
                            <li>If we fail to deliver the Goods, our liability is limited to the cost of obtaining replacement goods of a similar description and quality in the cheapest market available, less the price of the Goods. However, we will not be liable to the extent that any failure to deliver was caused by an Event Outside Our Control, or because you failed to provide adequate delivery instructions or any other instructions that are relevant to the supply of goods.</li>
                            <li>If you fail to take delivery from our supplier after two attempts, we may resell part of, or all the Goods. </li>
                        </ol>
                    </li>
                    <li><strong>Price of products and delivery charges</strong>
                        <ol>
                            <li>The prices of the Goods and/or Digital Services will be as quoted to you at the time you submit your order. We take all reasonable care to ensure that the prices of Goods and/or Digital Services are correct at the time when the relevant information was entered onto the system. However, please see clause 10.5 for what happens if we discover an error in the price of Goods you ordered.</li>
                            <li>Prices for our Goods and/or Digital Services may change from time to time, but changes will not affect any order you have already placed.</li>
                            <li>The price of Goods and/or Digital Services excludes VAT (where applicable) at the applicable current rate chargeable in the UK for the time being. However, if the rate of VAT changes between the date of your order and the date of delivery, we will adjust the VAT you pay, unless you have already paid for the Goods and/or Digital Services in full before the change in VAT takes effect.</li>
                            <li>The price of the Goods does not include delivery charges. Our delivery charges are as advised to you during the check-out process, before you confirm your order. </li>
                            <li>We sell a large number of Goods and/or Digital Services. It is always possible that, despite our reasonable efforts, some of the Goods and/or Digital Services may be incorrectly priced. If we discover an error in the price of the Goods and/or Digital Services you have ordered we will contact you in writing to inform you of this error and we will give you the option of continuing to purchase the Goods and/or Digital Services at the correct price or cancelling your order. We will not process your order until we have your instructions. If we are unable to contact you using the contact details you provided during the order process, we will treat the order as cancelled and notify you in writing. If we mistakenly accept and process your order where a pricing error is obvious and unmistakeable and could reasonably have been recognised by you as a mispricing, we may cancel supply of the Goods and/or Digital Services and refund you any sums you have paid.</li>
                        </ol>
                    </li>
                    <li><strong>How to pay</strong>
                        <ol>
                            <li>You can pay for Goods and/or Digital Services using a debit card, credit card (excluding American Express) or via bank transfer.</li>
                            <li>Payment for the Goods and/or Digital Services and all applicable delivery charges shall be within 30 days from the date we issue our invoice to you. </li>
                        </ol>
                    </li>
                    <li><strong>International delivery</strong>
                        <ol>
                            <li>If you are looking to place a delivery for Goods outside of the UK please contact our customer services team who can advise on the countries we are able to deliver to (<strong>International Delivery Destinations</strong>). Whilst we endeavour to deliver to as many countries as possible there are restrictions on some Goods for certain International Delivery Destinations so please ensure you <a href="https://vektor.co.uk/contact">contact</a> our customer service team in advance.</li>
                            <li>If you order Goods from our site for delivery to one of the International Delivery Destinations, your order may be subject to import duties and taxes which are applied when the delivery reaches that destination. Please note that we have no control over these charges and we cannot predict their amount.</li>
                            <li>You will be responsible for payment of any such import duties and taxes. Please contact your local customs office for further information before placing your order.</li>
                            <li>You must comply with all applicable laws and regulations of the country for which the Goods are destined. We will not be liable or responsible if you break any such law.</li>
                        </ol>
                    </li>
                    <li><strong>Your rights to end the contract if you are a consumer</strong>
                        <ol>
                            <li><strong>You can always end your contract with us.</strong> Your rights when you end the contract will depend on what you have bought, whether there is anything wrong with it, how we are performing and when you decide to end the contract and whether you are a consumer or business customer:</li>
                            <li><strong>If what you have bought is faulty or misdescribed you may have a legal right to end the contract</strong> (or to get the product repaired or replaced or a service re-performed or to get some or all of your money back), see clause 17;</li>
                            <li><strong>If you want to end the contract because of something we have done or have told you we are going to do, see clause 13.6;</strong></li>
                            <li><strong>If you are a consumer and have just changed your mind about the product, see clause 13.7</strong>. You may be able to get a refund if you are within the cooling-off period and, if this is applicable to you and your products or services, and the product or service is not a made to measure product or completed, but this may be subject to deductions and you will have to pay the costs of return of any goods. For the avoidance of doubt any made to order, or bespoke products, will not be subject to a cooling off period and you will not be entitled to a refund on these orders.</li>
                            <li><strong>In all other cases (if we are not at fault and you are not a consumer exercising your right to change your mind), see clause 13.10.</strong></li>
                            <li><strong>Ending the contract because of something we have done or are going to do</strong>. If you are ending a contract for a reason set out at 13.6.1 to 13.6.2 below the contract will end immediately and we will refund you in full for any products which have not been provided and you may also be entitled to compensation. The reasons are:
                                <ol>
                                    <li>we have told you about an upcoming change to the product not yet provided which will have a significant impact on the functioning or quality of the product or these terms which you do not agree to (see clause 7.2); or</li>
                                    <li>we have told you about an error in the price (which results in increased costs to be paid by you) or description of the product you have ordered (which affects any use of the products) and you do not wish to proceed.</li>
                                </ol>
                            </li>
                            <li><strong>Exercising your right to change your mind if you are a consumer (Consumer Contracts Regulations 2013)</strong>. We tell you when and how you can end an on-going contract with us (for example, for regular services or a subscription to digital content or goods) during the order process and we confirm this information to you in writing after we've accepted your order. All of our Goods are made-to-measure specifications or are clearly personalised and accordingly, you do not have a right to change your mind (regulation 28(1)(b), Consumer Contract Regulations).</li>
                            <li><strong>When consumers do not have the right to change their minds.</strong> You do not have a right to change your mind in respect of:
                                <ol>
                                    <li>Digital products or services once you have started to download or stream these or the services have been completed;</li>
                                    <li>products sealed for health protection or hygiene purposes, once these have been unsealed after you receive them;</li>
                                    <li>goods that are made to your specifications or are clearly personalised; and</li>
                                    <li>goods which become mixed inseparably with other items after their delivery.</li>
                                </ol>
                            </li>
                            <li><strong>How long do consumers have to change their minds?</strong> If you change your mind about a product or service that has not yet been completed you must let us know no later than 14 days after the day we deliver it.</li>
                            <li><strong>Ending the contract where we are not at fault and there is no right to change your mind.</strong> Even if we are not at fault and you do not have a right to change your mind, you can still end the contract before it is completed, but you will have to pay us compensation. Where we have begun making the made to order products you will be liable for all time and material costs incurred at the time of ending the contract. We may refund you part of any pre-payments deducting the aforementioned costs or, where you have not made a payment on account, we will charge you for these costs.</li>
                        </ol>
                    </li>
                    <li><strong>How to end the contract with us (including if you are a consumer who has changed their mind)</strong>
                        <ol>
                            <li><strong>Tell us you want to end the contract.</strong> To end the contract with us, please let us know by calling customer services on <a href="tel:00441444657399">+44 1444 657 399</a> or emailing us at <a href="mailto:hello@vektor.co.uk">hello@vektor.co.uk</a>. Please provide your name, home address, details of the order and, where available, your phone number and email address.</li>
                        </ol>
                    </li>
                    <li><strong>Our rights to end the contract.</strong>
                        <ol>
                            <li><strong>We may end the contract if you break it.</strong> We may end the contract for a product at any time by writing to you if:
                                <ol>
                                    <li>you do not make any payment to us when it is due and you still do not make payment within 14 days of us reminding you that payment is due;</li>
                                    <li>you do not, within a reasonable time of us asking for it, provide us with information that is necessary for us to provide the products or services, for example fitting instructions;</li>
                                    <li>you do not, within a reasonable time, allow us to deliver the products to you; or</li>
                                    <li>you do not, within a reasonable time, allow us access to your premises to supply the products.</li>
                                </ol>
                            </li>
                            <li><strong>You must compensate us if you break the contract.</strong> If we end the contract in the situations set out in clause 15.1 we will refund any money you have paid in advance for products we have not provided but we may deduct or charge you reasonable compensation for the net costs we will incur as a result of your breaking the contract, for example, where the products are in the process of being made when you break the contract.</li>
                        </ol>
                    </li>
                    <li><strong>If there is a problem with the product</strong>
                        <ol>
                            <li><strong>How to tell us about problems.</strong> If you have any questions or complaints about the product, please contact us providing us with photos of both the product and any related box/packaging to enable us to make an assessment. You can telephone our customer service team at <a href="tel:00441444657399">+44 1444 657 399</a> or emailing us at <a href="mailto:hello@vektor.co.uk">hello@vektor.co.uk</a>. </li>
                            <li>When giving this written notice you should include pictures of the defects.</li>
                            <li>Where your goods are defective we shall, as appropriate repair or replace the defective goods. However where any defects are caused by your actions or omissions then any repairs or replacements will not be covered.</li>
                        </ol>
                    </li>
                    <li><strong>Your rights in respect of defective products or services if you are a consumer</strong>
                        <ol>
                            <li><strong>Your legal rights.</strong> We are under a legal duty to supply products that are in conformity with this contract. Nothing in these terms will affect your legal rights.</li>
                            <li><strong>Your obligation to return rejected products.</strong> If you wish to exercise your legal rights to reject products you must post them back to us or (if they are not suitable for posting) allow us to collect them from you. We will pay the costs of postage or collection</li>
                            <li>If your product is services, for example artworking, the Consumer Rights Act 2015 says that you can ask us to repeat or fix a service if it's not carried out with reasonable care and skill, or get some money back if we can't fix it.</li>
                        </ol>
                    </li>
                    <li><strong>Your rights in respect of defective products if you are a business</strong>
                        <ol>
                            <li>If you are a business customer we warrant that on delivery any products which are products shall:
                                <ol>
                                    <li>conform in all material respects with their description and any relevant specification;</li>
                                    <li>be free from material defects in design, material and workmanship;</li>
                                    <li>be of satisfactory quality (within the meaning of the Sale of products Act 1979); and</li>
                                    <li>be fit for any purpose held out by us.</li>
                                </ol>
                            </li>
                            <li>Subject to clause 18.3, if:
                                <ol>
                                    <li>you give us notice in writing within a reasonable time of discovery that a product does not comply with the warranty set out in clause 18.1;</li>
                                    <li>we are given a reasonable opportunity of examining such product; and</li>
                                    <li>you return such product to us, we shall, at our option, replace the defective product, or refund the price of the defective product in full including any delivery fees incurred by you in returning the products to us. Any refund (including delivery fees) shall be paid to you as a credit to your account.</li>
                                </ol>
                            </li>
                            <li>We will not be liable for a product's failure to comply with the warranty in clause 18.1 if:
                                <ol>
                                    <li>you make any further use of such product after giving a notice in accordance with clause 18.2.1;</li>
                                    <li>the defect arises because you failed to follow our oral or written instructions as to the storage, use or maintenance of the product or (if there are none) good trade practice;</li>
                                    <li>the defect arises as a result of us following any or specification supplied by you;</li>
                                    <li>you alter or repair the product without our written consent; or</li>
                                    <li>the defect arises as a result of fair wear and tear, wilful damage, negligence, or abnormal working conditions.</li>
                                </ol>
                            </li>
                            <li>Where, following examination of the product by us, we reasonably determine that the product’s failure was due to a matter listed in clause 18.3 or the product is not faulty, we will return the products to you, at your cost, and no replacement or refund shall be available. </li>
                            <li>Except as provided in this clause 18, we shall have no liability to you in respect of a product's failure to comply with the warranty set out in clause 18.1.</li>
                            <li>These terms shall apply to any replacement products supplied by us under clause 18.2.</li>
                        </ol>
                    </li>
                    <li><strong>Our responsibility for loss or damage suffered by you if you are a consumer</strong>
                        <ol>
                            <li><strong>We are responsible to you for foreseeable loss and damage caused by us.</strong> If we fail to comply with these terms, we are responsible for loss or damage you suffer that is a foreseeable result of our breaking this contract or our failing to use reasonable care and skill, but we are not responsible for any loss or damage that is not foreseeable. Loss or damage is foreseeable if either it is obvious that it will happen or if, at the time the contract was made, both we and you knew it might happen, for example, if you discussed it with us during the sales process.</li>
                            <li><strong>We do not exclude or limit in any way our liability to you where it would be unlawful to do so.</strong> This includes liability for death or personal injury caused by our negligence or the negligence of our employees, agents or subcontractors; for fraud or fraudulent misrepresentation; for breach of your legal rights in relation to the products and for defective products under the Consumer Protection Act 1987.</li>
                        </ol>
                    </li>
                    <li><strong>Our responsibility for loss or damage suffered by you if you are a business</strong>
                        <ol>
                            <li>Nothing in these terms shall limit or exclude our liability for:
                                <ol>
                                    <li>death or personal injury caused by our negligence, or the negligence of our employees, agents or subcontractors (as applicable);</li>
                                    <li>fraud or fraudulent misrepresentation;</li>
                                    <li>any matter in respect of which it would be unlawful for us to exclude or restrict liability.</li>
                                </ol>
                            </li>
                            <li>Except to the extent expressly stated in clause 18.1 all terms implied by sections 13 to 15 of the Sale of Goods Act 1979 and sections 3 to 5 of the Supply of Goods and Services Act 1982 are excluded.</li>
                            <li>Subject to clause 20.1:
                                <ol>
                                    <li>we shall not be liable to you, whether in contract, tort (including negligence), breach of statutory duty, or otherwise, for any loss of profit, or any indirect or consequential loss arising under or in connection with any contract between us; and</li>
                                    <li>our total liability to you for all other losses arising under or in connection with any contract between us, whether in contract, tort (including negligence), breach of statutory duty, or otherwise, shall be limited to 100% of the total sums paid by you for products under such contract.</li>
                                </ol>
                            </li>
                        </ol>
                    </li>
                    <li><strong>Termination</strong>
                        <ol>
                            <li>Without limiting any of our other rights, we may suspend the supply or delivery of the Goods and/or Digital Services to you, or terminate the Contract with immediate effect by giving written notice to you if:
                                <ol>
                                    <li>you commit a material breach of any term of the Contract and (if such a breach is remediable) fail to remedy that breach within 14 days of you being notified in writing to do so;</li>
                                    <li>you fail to pay any amount due under the Contract on the due date for payment.</li>
                                </ol>
                            </li>
                            <li>Termination of the Contract shall not affect your or our rights and remedies that have accrued as at termination.</li>
                            <li>Any provision of the Contract that expressly or by implication is intended to come into or continue in force on or after termination shall remain in full force and effect.</li>
                        </ol>
                    </li>
                    <li><strong>Events outside our control</strong>
                        <ol>
                            <li>We will not be liable or responsible for any failure to perform, or delay in performance of, any of our obligations under the Contract that is caused by any act or event beyond our reasonable control (<strong>Event Outside Our Control</strong>).</li>
                            <li>If an Event Outside Our Control takes place that affects the performance of our obligations under the Contract:
                                <ol>
                                    <li>we will contact you as soon as reasonably possible to notify you; and</li>
                                    <li>our obligations under the Contract will be suspended and the time for performance of our obligations will be extended for the duration of the Event Outside Our Control. Where the Event Outside Our Control affects our delivery of Goods to you, we will arrange a new delivery date with you after the Event Outside Our Control is over.</li>
                                </ol>
                            </li>
                            <li>You may cancel the Contract affected by an Event Outside Our Control which has continued for more than 30 days. To cancel please contact us. If you opt to cancel, you will return (at our cost) any relevant Goods you have already received and we will refund the price you have paid, including any delivery charges.</li>
                        </ol>
                    </li>
                    <li><strong>Communications between us</strong>
                        <ol>
                            <li>When we refer to "in writing" in these Terms, this includes email.</li>
                            <li>Any notice or other communication given under or in connection with the Contract must be in writing and be delivered personally, sent by pre-paid first class post or other next working day delivery service, or email.</li>
                            <li>A notice or other communication is deemed to have been received:
                                <ol>
                                    <li>if delivered personally, on signature of a delivery receipt or at the time the notice is left at the proper address;</li>
                                    <li>if sent by pre-paid first class post or other next working day delivery service, at 9.00 am on the second working day after posting; or</li>
                                    <li>if sent by email, at 9.00 am the next working day after transmission.</li>
                                </ol>
                            </li>
                            <li>In proving the service of any notice, it will be sufficient to prove, in the case of a letter, that such letter was properly addressed, stamped and placed in the post and, in the case of an email, that such email was sent to the specified email address of the addressee.</li>
                            <li>The provisions of this clause shall not apply to the service of any proceedings or other documents in any legal action.</li>
                        </ol>
                    </li>
                    <li><strong>General</strong>
                        <ol>
                            <li><strong>Assignment and transfer.</strong>
                                <ol>
                                    <li>We may assign or transfer our rights and obligations under the Contract to another entity but will always notify you in writing or by posting on this webpage if this happens.</li>
                                    <li>You may only assign or transfer your rights or your obligations under the Contract to another person if we agree in writing.</li>
                                </ol>
                            </li>
                            <li><strong>Variation.</strong> Any variation of the Contract only has effect if it is in writing and signed by you and us (or our respective authorised representatives).</li>
                            <li><strong>Waiver</strong>. If we do not insist that you perform any of your obligations under the Contract, or if we do not exercise our rights or remedies against you, or if we delay in doing so, that will not mean that we have waived our rights or remedies against you or that you do not have to comply with those obligations. If we do waive any rights or remedies, we will only do so in writing, and that will not mean that we will automatically waive any right or remedy related to any later default by you.</li>
                            <li><strong>Severance</strong>. Each paragraph of these Terms operates separately. If any court or relevant authority decides that any of them is unlawful or unenforceable, the remaining paragraphs will remain in full force and effect.</li>
                            <li><strong>Third party rights</strong>. The Contract is between you and us. No other person has any rights to enforce any of its terms.</li>
                            <li><strong>Governing law and jurisdiction</strong>. The Contract is governed by English law and each party irrevocably agrees to submit all disputes arising out of or in connection with the Contract to the exclusive jurisdiction of the English courts.</li>
                        </ol>
                    </li>
                </ol>
                <p>If you have any inquiries or concerns regarding these terms and conditions, please do not hesitate to contact us at <a href="mailto:hello@vektor.co.uk">hello@vektor.co.uk</a>.</p>
            </article>
        </div>
@endsection

<?php
/**
 * Website Design and Services Agreement — English, Texas law. Original template written for Ninubo from the
 * commercial terms in the "Ninubo 360" proposal. Should be reviewed by a Texas attorney before first use.
 * Output is plain HTML (no <html>/<body>): shown in the CRM, on the signing page and inside the PDF.
 * When finalized, this HTML is frozen in contracts.body_html and hashed — later template edits never change it.
 *
 * @var array $contract @var array $client @var ?array $invoice @var array $oneTime @var array $plans
 * @var array $schedule @var bool $hasWebsite @var ?float $renewalPrice @var array $s
 */
use Crm\Core\Settings;
use Crm\Models\Client;
use Crm\Models\Invoice;

$provider = $s['business_legal_name'] !== '' ? $s['business_legal_name'] : $s['business_name'];
$dba = ($s['business_legal_name'] !== '' && $s['business_legal_name'] !== $s['business_name']) ? ' doing business as "' . $s['business_name'] . '"' : '';
$providerAddress = implode(', ', Settings::addressLines());
$clientName = trim((string)$client['company_name']) !== '' ? $client['company_name'] : $client['contact_name'];
$clientAddress = implode(', ', Client::addressLines($client));
$county = $s['contract_governing_county'] ?: 'Hidalgo';
$days = fn(string $key, int $default) => max(1, (int)($s[$key] ?: $default));
$cancelDays = $days('contract_plan_cancel_days', 30);
$lateDays = $days('contract_late_suspend_days', 15);
$pauseDays = $days('contract_materials_pause_days', 60);
$warrantyDays = $days('contract_warranty_days', 30);
$n = 0;
$sec = function (string $title) use (&$n): string { $n++; return '<h2>' . $n . '. ' . e($title) . '</h2>'; };
?>
<div class="agreement">
  <h1><?= e(strtoupper($contract['title'])) ?></h1>
  <p class="agreement-meta">
    Agreement No. <strong><?= e($contract['number'] ?: 'DRAFT') ?></strong>
    &nbsp;·&nbsp; Effective date: <strong><?= e(date('F j, Y', strtotime($contract['effective_date']))) ?></strong>
    <?php if ($invoice): ?>&nbsp;·&nbsp; Related invoice: <strong><?= e($invoice['number']) ?></strong><?php endif; ?>
  </p>

  <p>
    This Website Design and Services Agreement (the "Agreement") is entered into on the effective date above between
    <strong><?= e($provider) ?></strong><?= e($dba) ?><?= $providerAddress !== '' ? ', located at ' . e($providerAddress) : '' ?> (the "Provider"), and
    <strong><?= e($clientName) ?></strong><?php if (trim((string)$client['company_name']) !== ''): ?>, represented by <?= e($contract['client_signer_name'] ?: $client['contact_name']) ?><?php endif; ?><?= $clientAddress !== '' ? ', located at ' . e($clientAddress) : '' ?> (the "Client").
    The Provider and the Client are each a "Party" and together the "Parties".
  </p>

  <?= $sec('Scope of Work') ?>
  <p>The Provider will deliver the following services (the "Services"):</p>
  <?php if ($oneTime): ?>
  <ol class="agreement-scope">
    <?php foreach ($oneTime as $item): ?>
    <li><strong><?= e($item['description']) ?></strong><?= (float)$item['quantity'] != 1.0 ? ' (quantity: ' . e(rtrim(rtrim(number_format((float)$item['quantity'], 2), '0'), '.')) . ')' : '' ?><?php if (trim((string)$item['details']) !== ''): ?> — <?= e($item['details']) ?><?php endif; ?></li>
    <?php endforeach; ?>
  </ol>
  <?php else: ?>
  <p><em>The services described in the related invoice.</em></p>
  <?php endif; ?>
  <p>
    Anything not described above — including new features, additional pages, or changes outside the original scope —
    is not included and will be quoted separately in writing before any work is done (see Section 4).
  </p>

  <?= $sec('Fees and Payment') ?>
  <?php if ($invoice): ?>
  <p>
    The fees for the Services are set out in invoice <strong><?= e($invoice['number']) ?></strong>, for a total of
    <strong><?= e(money($invoice['total'])) ?></strong>, including Texas sales tax where it applies. Payments are due as follows:
  </p>
  <table class="agreement-table">
    <?php foreach ($schedule as $step): ?>
    <tr><td><?= e($step['label']) ?> (<?= (int)$step['pct'] ?>%)</td><td><?= e($step['when']) ?></td><td class="num"><?= e(money($step['amount'])) ?></td></tr>
    <?php endforeach; ?>
  </table>
  <?php else: ?>
  <p>The fees for the Services are set out in the Provider's invoice.</p>
  <?php endif; ?>
  <p>
    Work begins once the deposit has been received and the Client has provided the minimum materials needed to start.
    The website will be published on its final domain only after the payment due before launch has been received.
    Sales tax is charged as required by Texas law; the Client is responsible for it unless the Client provides a valid
    Texas exemption certificate.
  </p>

  <?= $sec('Client Responsibilities and Materials') ?>
  <p>
    The Client will provide, through the information form (brief) link sent by the Provider, the materials needed for the
    project: logo, photographs, company and service information, contact details, social media links, existing text,
    required access credentials and any legal information. The Client is responsible for the accuracy of this information.
  </p>
  <p>
    Delivery dates are counted from the day all required materials are received; delays in providing materials move the
    delivery date accordingly. If the Client does not provide the required materials or feedback for
    <strong><?= $pauseDays ?> days</strong>, the Provider may pause the project. A paused project may be resumed by written
    request and may require rescheduling. Payments received for work already performed are non-refundable.
  </p>

  <?= $sec('Revisions and Change Requests') ?>
  <p>
    Each package includes the number of revision rounds stated in its description. A revision round is one consolidated
    set of changes sent by the Client at the same time. Additional rounds, new pages, new features or other changes outside
    the original scope are not included and will be quoted separately; such work starts only after the Client approves the
    written quote.
  </p>

  <?= $sec('Timeline') ?>
  <p>
    Estimated delivery times are stated in each package description and are counted in business days after all required
    materials have been received. They are good-faith estimates, not guarantees, and depend on the Client's timely
    materials, feedback and payments.
  </p>

  <?php if ($hasWebsite): ?>
  <?= $sec('Domain, Hosting and Yearly Renewal') ?>
  <p>
    The website package includes the domain name registration and website hosting for the <strong>first year</strong>
    from the effective date. After the first year, domain and hosting renew yearly
    <?php if ($renewalPrice): ?>at <strong><?= e(money($renewalPrice)) ?> per year</strong> plus applicable sales tax<?php else: ?>at the Provider's then-current yearly rate<?php endif; ?>,
    billed in advance. The Provider may adjust the renewal price with at least 30 days' written notice before the renewal date.
    The domain is registered in the Client's name. If the renewal is not paid, the Provider may suspend hosting, and the
    domain may expire.
  </p>
  <?php endif; ?>

  <?php if ($plans): ?>
  <?= $sec('Monthly Care Plan') ?>
  <?php foreach ($plans as $plan): ?>
  <p>
    The Client subscribes to the <strong><?= e($plan['name']) ?></strong> plan for <strong><?= e(money($plan['price'])) ?> per month</strong>
    plus applicable sales tax: <?= e($plan['description']) ?>
  </p>
  <?php endforeach; ?>
  <p>
    The plan is month-to-month and billed monthly in advance; the first month is included in the related invoice. Monthly
    hours of changes that are not used do not roll over to the next month. Either Party may cancel the plan with
    <strong><?= $cancelDays ?> days' written notice</strong> (email is sufficient); fees already paid are not refunded.
  </p>
  <?php endif; ?>

  <?= $sec('Late Payments and Suspension') ?>
  <p>
    Invoices are due on their due date. If a payment is more than <strong><?= $lateDays ?> days</strong> past due, the Provider
    may, after notifying the Client, suspend work, hosting and other services until the balance is paid. Suspension does not
    relieve the Client of the obligation to pay. Services are restored once the account is current.
  </p>

  <?= $sec('Ownership and Intellectual Property') ?>
  <p>
    Upon payment in full, the Client owns the final website design and the content created specifically for the Client,
    as well as the Client's domain and digital assets (logo, text, images and accounts provided by the Client). The Provider
    keeps all rights to its pre-existing code, templates, tools and know-how, and grants the Client a perpetual,
    non-exclusive license to use them as part of the Client's website. Third-party components (such as themes, plugins,
    fonts and stock images) are used under their own licenses. Access credentials are delivered to the Client at the end of
    the project once all payments are received.
  </p>
  <p>
    The Provider may display the finished website and a short description of the project in its portfolio and marketing,
    unless the Client asks in writing that it not do so.
  </p>

  <?= $sec('Warranty') ?>
  <p>
    For <strong><?= $warrantyDays ?> days</strong> after the website is published, the Provider will correct, at no additional
    cost, errors caused directly by the Provider's original development. This warranty does not cover new features, design
    changes, new pages, content changes requested afterwards, problems caused by third parties (hosting providers, external
    services, browser or platform updates), or changes made by the Client or another provider.
  </p>

  <?= $sec('Client Content') ?>
  <p>
    The Client confirms that it has the rights to all content it provides (text, images, logos, trademarks) and agrees to
    defend and hold the Provider harmless from any claim arising from that content.
  </p>

  <?= $sec('Limitation of Liability') ?>
  <p>
    The Provider's total liability under this Agreement is limited to the amounts paid by the Client under this Agreement.
    Neither Party is liable for indirect, incidental or consequential damages, including lost profits or lost data. The
    Provider is not responsible for interruptions or failures of third-party services it does not control.
  </p>

  <?= $sec('Term and Termination') ?>
  <p>
    This Agreement starts on the effective date and continues until the Services are delivered and, for recurring services,
    until they are cancelled as described above. Either Party may terminate this Agreement by written notice if the other
    Party materially breaches it and does not cure the breach within 15 days of receiving notice. On termination, the Client
    pays for all work performed and expenses incurred up to the termination date.
  </p>

  <?= $sec('Electronic Records and Signatures') ?>
  <p>
    The Parties agree to conduct this transaction by electronic means. Electronic records and electronic signatures on this
    Agreement have the same legal effect as originals and handwritten signatures under the Texas Uniform Electronic
    Transactions Act (Texas Business and Commerce Code, Chapter 322) and the federal Electronic Signatures in Global and
    National Commerce Act (ESIGN). This Agreement may also be signed by hand and in counterparts.
  </p>

  <?= $sec('Governing Law and Venue') ?>
  <p>
    This Agreement is governed by the laws of the State of Texas. Any dispute will be resolved in the state courts located
    in <strong><?= e($county) ?> County, Texas</strong>, and the Parties consent to that venue.
  </p>

  <?= $sec('Entire Agreement') ?>
  <p>
    This Agreement, together with the related invoice, is the entire agreement between the Parties about the Services and
    replaces any prior proposals or understandings. Any change must be in writing and accepted by both Parties. If any part
    of this Agreement is found unenforceable, the rest remains in effect.
  </p>

  <?php if (trim((string)$contract['special_terms']) !== ''): ?>
  <?= $sec('Special Terms') ?>
  <p><?= nl2br(e($contract['special_terms'])) ?></p>
  <?php endif; ?>
</div>

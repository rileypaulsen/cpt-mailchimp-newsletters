<div class="wrap">
	<h1>Newsletter Subscribers</h1>
	<p>Below is a list of subscribers to the <b><?= $list->name; ?></b> MailChimp list.</p>
	<h2><?= count($subscribers->members); ?> Subscribers</h2>
	<table class="widefat fixed" cellspacing="0">
		<thead>
			<tr>
				<th class="manage-column">Name</th>
				<th class="manage-column">Last Name</th>
				<th class="manage-column">Email</th>
				<th class="manage-column">Signup Date</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($subscribers->members as $member): ?>
			<tr>
				<td class="manage-column"><b><?= $member->merge_fields->FNAME; ?></b></td>
				<td class="manage-column"><b><?= $member->merge_fields->LNAME; ?></b></td>
				<td class="manage-column"><?= $member->email_address; ?></td>
				<td class="manage-column"><?= date('M j, Y',strtotime($member->timestamp_signup)); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
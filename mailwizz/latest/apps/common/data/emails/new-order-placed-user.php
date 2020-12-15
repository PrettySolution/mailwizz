<?php defined('MW_PATH') || exit('No direct script access allowed'); ?>

Hello [USER_NAME] <br />
A new order has been placed on your [SITE_NAME] website as follows: <br />

<table>
	<tr>
		<th style="width:50%">Customer</th>
		<td>[CUSTOMER_NAME]</td>
	</tr>
	<tr>
		<th>Price plan</th>
		<td>[PLAN_NAME]</td>
	</tr>
	<tr>
		<th>Subtotal:</th>
		<td>[ORDER_SUBTOTAL]</td>
	</tr>
	<tr>
		<th>Tax:</th>
		<td>[ORDER_TAX]</td>
	</tr>
	<tr>
		<th>Discount:</th>
		<td>[ORDER_DISCOUNT]</td>
	</tr>
	<tr>
		<th>Total:</th>
		<td>[ORDER_TOTAL]</td>
	</tr>
	<tr>
		<th>Status:</th>
		<td>[ORDER_STATUS]</td>
	</tr>
</table>

You can view the full order if you login into the application at: <br />
<a href="[ORDER_OVERVIEW_URL]">[ORDER_OVERVIEW_URL]</a>

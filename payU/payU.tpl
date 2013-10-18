
<p class="payment_module">
	{if $confirmPayment==1}
		<a href="{$link->getModuleLink('payU', 'payment')}" title="{l s='Pay with PAYU' mod='payU'}">
			<img src="{$module_template_dir}payU.gif" alt="{l s='Pay with PAYU' mod='payU'}" />
			{l s='Pay with PAYU' mod='payU'}
		</a>
	{else}
		<img src="{$module_template_dir}payU.gif" alt="{l s='Pay with PAYU' mod='payU'}" />
		{l s='Please Check PayU Details Not Correct' mod='payU'}
		
	{/if}
</p>
 
@extends('layouts.app')

@section('title', 'Configuration paiement')
@section('page-title', 'Configuration des paiements')

@section('content')
<div class="bg-slate-900 border border-slate-800 rounded-xl p-5">
    <form method="POST" action="{{ route('admin.payments.settings.save') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @csrf
        <div>
            <label class="block text-slate-300 text-xs mb-1">Devise</label>
            <input name="currency" value="{{ $settings['currency'] ?? 'XOF' }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-slate-300 text-xs mb-1">Frais transaction (%)</label>
            <input name="transaction_fee_percent" type="number" step="0.01" value="{{ $settings['transaction_fee_percent'] ?? '0' }}" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="flex items-end">
            <label class="text-slate-300 text-sm"><input type="checkbox" name="auto_invoice_enabled" value="1" @checked(($settings['auto_invoice_enabled'] ?? '0') === '1')> Factures automatiques activées</label>
        </div>

        <div class="md:col-span-3 border-t border-slate-800 pt-4">
            <h2 class="text-white text-sm font-semibold mb-3">Moyens de paiement activés</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-slate-300 text-sm">
                <label><input type="checkbox" name="method_orange_money" value="1" @checked(($settings['method_orange_money'] ?? '1') === '1')> Orange Money</label>
                <label><input type="checkbox" name="method_mtn_momo" value="1" @checked(($settings['method_mtn_momo'] ?? '1') === '1')> MTN MoMo</label>
                <label><input type="checkbox" name="method_wave" value="1" @checked(($settings['method_wave'] ?? '1') === '1')> Wave</label>
                <label><input type="checkbox" name="method_moov_money" value="1" @checked(($settings['method_moov_money'] ?? '1') === '1')> Moov Money</label>
                <label><input type="checkbox" name="method_card" value="1" @checked(($settings['method_card'] ?? '0') === '1')> Carte</label>
                <label><input type="checkbox" name="method_paypal" value="1" @checked(($settings['method_paypal'] ?? '0') === '1')> PayPal</label>
            </div>
        </div>

        <div class="md:col-span-3 border-t border-slate-800 pt-4">
            <h2 class="text-white text-sm font-semibold mb-3">Passerelles (API keys)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <input name="gateway_orange_key" value="{{ $settings['gateway_orange_key'] ?? '' }}" placeholder="Orange API key" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="gateway_mtn_key" value="{{ $settings['gateway_mtn_key'] ?? '' }}" placeholder="MTN API key" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="gateway_wave_key" value="{{ $settings['gateway_wave_key'] ?? '' }}" placeholder="Wave API key" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
                <input name="gateway_paypal_key" value="{{ $settings['gateway_paypal_key'] ?? '' }}" placeholder="PayPal API key" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <div class="md:col-span-3">
            <button class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Enregistrer la configuration</button>
        </div>
    </form>
</div>
@endsection

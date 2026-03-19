<label class="sr-only" for="{{ $id ?? 'theme-switcher' }}">Theme</label>
<select
    id="{{ $id ?? 'theme-switcher' }}"
    data-theme-switcher
    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition focus:border-slate-900 focus:outline-none focus:ring-0"
>
    <option value="light">Light</option>
    <option value="dark">Dark</option>
    <option value="dim">Dim</option>
</select>

<x-layouts.base>
    @if (in_array(request()->route()->getName(),['login']))
        @if (in_array(request()->route()->getName(),['login']))
            <main class="main-content  mt-0">
                <div class="page-header page-header-bg align-items-start min-vh-100">
                    <span class="mask bg-gradient-dark opacity-6"></span>
                    {{ $slot }}
                </div>
            </main>
        @else
            {{ $slot }}
        @endif
    @else
        <x-navbars.sidebar></x-navbars.sidebar>
        <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
            <x-navbars.navs.auth></x-navbars.navs.auth>
            {{ $slot }}
        </main>
        <x-plugins></x-plugins>
    @endif
</x-layouts.base>

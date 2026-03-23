<template>
    <nav
        class="sticky top-17 lg:top-0 left-0 z-20 flex items-center justify-between px-6 md:px-10 py-4 backdrop-blur-xl dark:bg-[#080810]/75 bg-[#FDFCF7]/80 border-b dark:border-white/[0.06] border-black/[0.06]"
    >
        <div class="flex items-center gap-3">
            <span class="font-display text-base font-bold tracking-tight">{{
                $t('common.starterKits')
            }}</span>
        </div>
        <div class="flex items-center gap-6">
            <a
                href="#how-it-works"
                class="hidden md:block text-sm dark:text-white/50 text-black/50 hover:dark:text-white hover:text-black transition-colors duration-200"
                >{{ $t('common.howItWorks') }}</a
            >
            <a
                href="#faq"
                class="hidden md:block text-sm dark:text-white/50 text-black/50 hover:dark:text-white hover:text-black transition-colors duration-200"
                >{{ $t('common.faq') }}</a
            >
        </div>
    </nav>
</template>

<style scoped>
.font-display {
    font-family: 'Syne', sans-serif;
}

.font-body {
    font-family: 'DM Sans', sans-serif;
}

.gradient-text {
    background: linear-gradient(130deg, #ff6347 0%, #ff1478 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>

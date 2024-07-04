import { readonly, reactive } from "vue";

const globalState = reactive({
    show: false,
    title: '',
    message: '',
    resolver: null
});

export function useConfirm() {
    const resetModal = () => {
        globalState.title = ''
        globalState.message = ''
        globalState.show = false
        globalState.resolver = null
    }


    return {
        state: readonly(globalState),
        confirmation: (message, title = 'Please Confirm') => {
            globalState.message = message
            globalState.title = title
            globalState.show = true

            return new Promise((resolver) => {
                globalState.resolver = resolver
            })
        },
        confirm: () => {
            if(globalState.resolver) {
                globalState.resolver(true)
            }

            resetModal()
        },
        cancel: () => {
            if(globalState.resolver) {
                globalState.resolver(false)
            }

            resetModal()
        }
    }
}
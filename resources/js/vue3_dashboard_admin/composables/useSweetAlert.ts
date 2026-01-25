import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

// Simple wrappers around SweetAlert2 for consistent usage across the app

export const showConfirm = async (opts: {
  title: string
  text?: string
  icon?: 'warning' | 'error' | 'success' | 'info'
  confirmButtonText?: string
  cancelButtonText?: string
}): Promise<boolean> => {
  const result = await Swal.fire({
    title: opts.title,
    text: opts.text,
    icon: opts.icon ?? 'question',
    showCancelButton: true,
    confirmButtonText: opts.confirmButtonText ?? 'Yes',
    cancelButtonText: opts.cancelButtonText ?? 'Cancel'
  })
  return !!result.isConfirmed
}

export const showToast = async (opts: {
  icon: 'success' | 'error' | 'info' | 'warning'
  title?: string
  text?: string
  timer?: number
}) => {
  await Swal.fire({
    icon: opts.icon,
    title: opts.title ?? '',
    text: opts.text,
    showConfirmButton: opts.timer === 0,
    timer: opts.timer && opts.timer > 0 ? opts.timer : undefined,
    timerProgressBar: opts.timer && opts.timer > 0 ? true : false
  })
}

export default {}

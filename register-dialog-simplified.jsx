import React, { useContext, useEffect, useState } from 'react'
import { useForm } from 'react-hook-form'
import { AppsContext } from '../../App'
import { APP_STATUS } from '../../common/constants'
import { convertToFormData, getCSRFToken } from '../../common/utilities'
import './register-dialog.scss'

// Simple component replacements
const SimpleLoading = () => <div className="spinner-border" role="status"><span className="visually-hidden">Loading...</span></div>
const SimpleSelect = ({ children, ...props }) => <select className="form-select" {...props}>{children}</select>
const SimpleSelectItem = ({ children, ...props }) => <option {...props}>{children}</option>
const SimpleTextInput = ({ label, ...props }) => (
    <div className="mb-3">
        <label className="form-label">{label}</label>
        <input type="text" className="form-control" {...props} />
    </div>
)
const SimpleInlineNotification = ({ children, ...props }) => (
    <div className="alert alert-info" {...props}>{children}</div>
)

export const RegisterDialog = ({ app, onClose }) => {
    const { register, handleSubmit } = useForm()
    const [allApps] = useContext(AppsContext)
    const [applications, setApplications] = useState([])
    const [loading, setLoading] = useState(false)
    const [error, setError] = useState('')
    const [success, setSuccess] = useState('')

    useEffect(() => {
        if (allApps.length > 0) {
            setApplications(allApps.filter(a => a.status === APP_STATUS.REGISTERED))
        }
    }, [allApps])

    const onSubmit = async (data) => {
        setLoading(true)
        setError('')
        setSuccess('')
        
        try {
            const formData = convertToFormData(data)
            const csrfToken = getCSRFToken()
            
            const response = await fetch('/api/sso/register', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                },
                body: formData
            })
            
            if (response.ok) {
                setSuccess('Registration successful!')
                setTimeout(() => {
                    onClose('register_success')
                }, 1500)
            } else {
                setError('Registration failed. Please try again.')
            }
        } catch (err) {
            setError('An error occurred. Please try again.')
        } finally {
            setLoading(false)
        }
    }

    return (
        <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
            <div className="modal-dialog modal-lg">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title">Register for {app.name}</h5>
                        <button type="button" className="btn-close" onClick={onClose}></button>
                    </div>
                    <div className="modal-body">
                        {error && <SimpleInlineNotification>{error}</SimpleInlineNotification>}
                        {success && <SimpleInlineNotification>{success}</SimpleInlineNotification>}
                        
                        <form onSubmit={handleSubmit(onSubmit)}>
                            <SimpleTextInput 
                                label="Full Name"
                                {...register('fullName', { required: true })}
                            />
                            
                            <SimpleTextInput 
                                label="Email"
                                type="email"
                                {...register('email', { required: true })}
                            />
                            
                            <div className="mb-3">
                                <label className="form-label">Department</label>
                                <SimpleSelect {...register('department', { required: true })}>
                                    <SimpleSelectItem value="">Select Department</SimpleSelectItem>
                                    <SimpleSelectItem value="IT">IT</SimpleSelectItem>
                                    <SimpleSelectItem value="HR">HR</SimpleSelectItem>
                                    <SimpleSelectItem value="Finance">Finance</SimpleSelectItem>
                                    <SimpleSelectItem value="Operations">Operations</SimpleSelectItem>
                                </SimpleSelect>
                            </div>
                            
                            <div className="mb-3">
                                <label className="form-label">Reason for Access</label>
                                <textarea 
                                    className="form-control" 
                                    rows="3"
                                    {...register('reason', { required: true })}
                                ></textarea>
                            </div>
                            
                            <div className="modal-footer">
                                <button type="button" className="btn btn-secondary" onClick={onClose}>
                                    Cancel
                                </button>
                                <button type="submit" className="btn btn-primary" disabled={loading}>
                                    {loading ? <SimpleLoading /> : 'Register'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    )
}

const FormInput = ({ id, label, type, value, onChange, onBlur, error, success, isRequired = true }) => {
    return (
        <div className="form-group">
            {}
            <label htmlFor={id}>{label}{isRequired && <span className="required"> *</span>}</label>
            <div className="input-container">
                {}
                <input
                    type={type}
                    id={id}
                    name={id} 
                    value={value}
                    onChange={onChange}
                    onBlur={onBlur} 
                    className={error ? "invalid" : ""} 
                    required={isRequired} 
                />
                {}
                {success && !error && value && (
                    <span className="field-success">
                        <i className="fas fa-check-circle"></i>
                    </span>
                )}
            </div>
            {}
            {error && <div className="error-message"><i className="fas fa-exclamation-circle"></i> {error}</div>}
        </div>
    );
};
const PasswordStrengthMeter = ({ password }) => {
    const getPasswordStrength = (password) => {
        if (!password) return { strength: "", label: "" }; 
        const hasLetter = /[a-zA-Z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        const isLongEnough = password.length >= 8;
        const criteria = [hasLetter, hasNumber, hasSpecial, isLongEnough];
        const metCriteria = criteria.filter(Boolean).length;
        if (metCriteria <= 2) return { strength: "weak", label: "Weak" };
        if (metCriteria === 3) return { strength: "medium", label: "Medium" };
        return { strength: "strong", label: "Strong" }; 
    };
    const { strength, label } = getPasswordStrength(password);
    if (!password) return null;
    return (
        <div className="password-strength">
            <div className="strength-meter">
                {}
                <div className={`strength-meter-fill ${strength}`}></div>
            </div>
            {}
            <div className={`strength-text ${strength}`}>{label}</div>
        </div>
    );
};
const RegistrationForm = ({ csrfToken }) => {
    const [formData, setFormData] = React.useState({
        name: "",
        phone: "",
        email: "",
        password: ""
    });
    const [errors, setErrors] = React.useState({
        name: "",
        phone: "",
        email: "",
        password: ""
    });
    const [touched, setTouched] = React.useState({
        name: false,
        phone: false,
        email: false,
        password: false
    });
    const [isSubmitting, setIsSubmitting] = React.useState(false); 
    const [submitError, setSubmitError] = React.useState(""); 
    const [submitSuccess, setSubmitSuccess] = React.useState(false); 
    const [isCheckingEmail, setIsCheckingEmail] = React.useState(false); 
    const [emailExists, setEmailExists] = React.useState(false); 
    const emailCheckTimeout = React.useRef(null);
    const validate = {
        name: (value) => {
            if (!value.trim()) return "Name is required";
            if (!/^[a-zA-Z\s]+$/.test(value)) return "Name should only contain letters and spaces";
            return ""; 
        },
        phone: (value) => {
            if (!value.trim()) return "Phone number is required";
            if (!/^\d{10}$/.test(value)) return "Phone number must be 10 digits";
            return ""; 
        },
        email: (value) => {
            if (!value.trim()) return "Email is required";
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return "Invalid email format";
            if (emailExists) return "Email already exists";
            return ""; 
        },
        password: (value) => {
            if (!value) return "Password is required";
            if (value.length < 8) return "Password must be at least 8 characters";
            return ""; 
        }
    };
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prevData => ({
            ...prevData,
            [name]: value
        }));
        if (touched[name]) {
            setErrors(prevErrors => ({
                ...prevErrors,
                [name]: validate[name](value)
            }));
        }
        if (name === 'email' && value.trim() && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            if (emailCheckTimeout.current) {
                clearTimeout(emailCheckTimeout.current);
            }
            emailCheckTimeout.current = setTimeout(() => {
                checkEmailExists(value);
            }, 500);
        } else if (name === 'email') {
            if (emailCheckTimeout.current) {
                clearTimeout(emailCheckTimeout.current);
            }
            setEmailExists(false);
            if (errors.email === "Email already exists") {
                setErrors(prevErrors => ({ ...prevErrors, email: "" }));
            }
        }
    };
    const handleBlur = (e) => {
        const { name } = e.target;
        setTouched(prevTouched => ({
            ...prevTouched,
            [name]: true
        }));
        setErrors(prevErrors => ({
            ...prevErrors,
            [name]: validate[name](formData[name])
        }));
        if (name === 'email' && formData.email.trim() && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            if (emailCheckTimeout.current) {
                clearTimeout(emailCheckTimeout.current); 
            }
            checkEmailExists(formData.email);
        }
    };
    const checkEmailExists = (email) => {
        setIsCheckingEmail(true); 
        setEmailExists(false); 
        const data = new FormData();
        data.append('email', email);
        fetch('ajax/check-email', { 
            method: 'POST',
            body: data
        })
            .then(response => response.json()) 
            .then(data => {
                setIsCheckingEmail(false); 
                if (data.exists) {
                    setEmailExists(true); 
                    setErrors(prevErrors => ({
                        ...prevErrors,
                        email: "Email already exists"
                    }));
                } else {
                    if (errors.email === "Email already exists") {
                        setErrors(prevErrors => ({ ...prevErrors, email: "" }));
                    }
                }
            })
            .catch(error => {
                console.error('Error checking email:', error);
                setIsCheckingEmail(false); 
            });
    };
    const handleSubmit = (e) => {
        e.preventDefault(); 
        const allTouched = {};
        Object.keys(formData).forEach(key => { allTouched[key] = true; });
        setTouched(allTouched);
        const newErrors = {};
        let hasErrors = false;
        Object.keys(formData).forEach(key => {
            const error = validate[key](formData[key]);
            newErrors[key] = error;
            if (error) hasErrors = true;
        });
        setErrors(newErrors); 
        if (hasErrors) return;
        setIsSubmitting(true); 
        setSubmitError(""); 
        const data = new FormData();
        Object.keys(formData).forEach(key => {
            data.append(key, formData[key]);
        });
        data.append('csrf_token', csrfToken); 
        fetch('register', { 
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status} ${response.statusText || ''}`);
                }
                return response.json();
            })
            .then(data => {
                setIsSubmitting(false); 
                if (data.success) {
                    setSubmitSuccess(true); 
                    setFormData({ name: "", phone: "", email: "", password: "" });
                    setTouched({ name: false, phone: false, email: false, password: false });
                    setTimeout(() => {
                        window.location.href = window.baseUrl + 'login'; 
                    }, 2000); 
                } else {
                    setSubmitError(data.message || "Registration failed. Please try again.");
                }
            })
            .catch(error => {
                console.error('Error during registration:', error);
                setIsSubmitting(false); 
                if (error.message.includes('403')) {
                    setSubmitError("Security token validation failed. Please refresh the page and try again.");
                } else {
                    setSubmitError("An error occurred. Please try again later.");
                }
            });
    };
    return (
        <div className="register-form-container">
            {}
            {submitSuccess ? (
                <div className="alert alert-success">
                    <p>Registration successful! Redirecting to login page...</p>
                </div>
            ) : (
                <React.Fragment>
                    {}
                    {submitError && (
                        <div className="alert alert-error">
                            <p>{submitError}</p>
                        </div>
                    )}
                    {}
                    <form onSubmit={handleSubmit} className="register-form" noValidate> {}
                        {}
                        <FormInput
                            id="name"
                            label="Full Name"
                            type="text"
                            value={formData.name}
                            onChange={handleChange}
                            onBlur={handleBlur}
                            error={touched.name ? errors.name : ""} 
                            success={touched.name && !errors.name} 
                        />
                        <FormInput
                            id="phone"
                            label="Phone Number (10 digits)"
                            type="tel" 
                            value={formData.phone}
                            onChange={handleChange}
                            onBlur={handleBlur}
                            error={touched.phone ? errors.phone : ""}
                            success={touched.phone && !errors.phone}
                        />
                        <FormInput
                            id="email"
                            label="Email Address"
                            type="email" 
                            value={formData.email}
                            onChange={handleChange}
                            onBlur={handleBlur}
                            error={touched.email ? errors.email : ""}
                            success={touched.email && !errors.email && !isCheckingEmail}
                        />
                        {}
                        {isCheckingEmail && (
                            <div className="loading-indicator">
                                <i className="fas fa-spinner fa-spin"></i> Checking email...
                            </div>
                        )}
                        <FormInput
                            id="password"
                            label="Password"
                            type="password"
                            value={formData.password}
                            onChange={handleChange}
                            onBlur={handleBlur}
                            error={touched.password ? errors.password : ""}
                            success={touched.password && !errors.password}
                        />
                        {}
                        <PasswordStrengthMeter password={formData.password} />
                        {}
                        <div className="form-group">
                            <button
                                type="submit"
                                className="btn btn-primary"
                                disabled={isSubmitting} 
                            >
                                {isSubmitting ? (
                                    <React.Fragment>
                                        <i className="fas fa-spinner fa-spin"></i> Registering...
                                    </React.Fragment>
                                ) : (
                                    "Register"
                                )}
                            </button>
                        </div>
                    </form>
                    {}
                    <div className="login-link">
                        <p>Already have an account? <a href={window.baseUrl + 'login'}>Login here</a></p>
                    </div>
                </React.Fragment>
            )}
        </div>
    );
};

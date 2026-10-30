import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { getErrorMessage } from "@/lib/errors"

/**
 * fields: [{ name, label, type: "text"|"email"|"password"|"checkbox-group", required?, options? }]
 *   options (checkbox-group): [{ value, label, group? }]  -- `group` renders a heading, giving a permission matrix
 * record: the row being edited (null when creating). Its keys must match the field names.
 */
export default function ResourceForm({ fields, record, onSubmit, onCancel, submitLabel = "Save" }) {
  // Lazy initializer: computed once, on first render only.
  const [values, setValues] = useState(() =>
    Object.fromEntries(
      fields.map((f) => [f.name, record?.[f.name] ?? (f.type === "checkbox-group" ? [] : "")])
    )
  )
  const [errors, setErrors] = useState({})
  const [formError, setFormError] = useState(null)
  const [submitting, setSubmitting] = useState(false)

  // Functional update: React hands us the latest state, so rapid changes never overwrite each other.
  const setValue = (name, value) => setValues((prev) => ({ ...prev, [name]: value }))

  const toggle = (name, optionValue, checked) =>
    setValues((prev) => ({
      ...prev,
      [name]: checked ? [...prev[name], optionValue] : prev[name].filter((v) => v !== optionValue),
    }))

  // Laravel keys array errors as "roles.0", so match the field name or its "name." prefix.
  const errorFor = (name) => {
    const key = Object.keys(errors).find((k) => k === name || k.startsWith(`${name}.`))
    return key ? errors[key][0] : null
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrors({})
    setFormError(null)
    setSubmitting(true)
    try {
      // Send only fields that are actually rendered (e.g. no "roles" if the user isn't allowed to set them).
      await onSubmit(Object.fromEntries(fields.map((f) => [f.name, values[f.name]])))
    } catch (err) {
      const fieldErrors = err.response?.data?.errors
      if (err.response?.status === 422 && fieldErrors) setErrors(fieldErrors)
      else setFormError(getErrorMessage(err))
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      {fields.map((field) => (
        <div key={field.name} className="space-y-2">
          <Label htmlFor={field.name}>{field.label}</Label>

          {field.type === "checkbox-group" ? (
            <CheckboxGroup
              options={field.options ?? []}
              value={values[field.name]}
              onToggle={(optionValue, checked) => toggle(field.name, optionValue, checked)}
            />
          ) : (
            <Input
              id={field.name}
              type={field.type}
              value={values[field.name]}
              onChange={(e) => setValue(field.name, e.target.value)}
              required={field.required}
              autoComplete={field.type === "password" ? "new-password" : "off"}
            />
          )}

          {errorFor(field.name) && <p className="text-sm text-destructive">{errorFor(field.name)}</p>}
        </div>
      ))}

      {formError && <p className="text-sm text-destructive">{formError}</p>}

      <div className="flex justify-end gap-2">
        <Button type="button" variant="outline" onClick={onCancel} disabled={submitting}>
          Cancel
        </Button>
        <Button type="submit" disabled={submitting}>
          {submitting ? "Saving…" : submitLabel}
        </Button>
      </div>
    </form>
  )
}

function CheckboxGroup({ options, value, onToggle }) {
  if (options.length === 0) return <p className="text-sm text-muted-foreground">No options available.</p>

  // { users: [opt, opt], roles: [opt, ...] }; options without a group land under "".
  const groups = options.reduce((acc, opt) => {
    ;(acc[opt.group ?? ""] ??= []).push(opt)
    return acc
  }, {})

  return (
    <div className="space-y-3 rounded-md border p-3">
      {Object.entries(groups).map(([group, items]) => (
        <div key={group} className="space-y-2">
          {group && <p className="text-xs font-semibold uppercase text-muted-foreground">{group}</p>}
          <div className="flex flex-wrap gap-x-6 gap-y-2">
            {items.map((opt) => (
              <label key={opt.value} className="flex items-center gap-2 text-sm">
                <Checkbox
                  checked={value.includes(opt.value)}
                  onCheckedChange={(checked) => onToggle(opt.value, checked === true)}
                />
                {opt.label}
              </label>
            ))}
          </div>
        </div>
      ))}
    </div>
  )
}
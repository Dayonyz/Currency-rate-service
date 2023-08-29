export function hasSlash(string, position = 'start') {
  if (position === 'start') return hasSlashAtStart(string)
  else if (position === 'end') return hasSlashAtEnd(string)
  else return false
}

export function hasSlashAtEnd(string) {
  return string && string[string.length - 1] === '/'
}

export function hasSlashAtStart(string) {
  return string && string[0] === '/'
}

export function prefixUrl(url, prefix) {
  return stripSlashes(prefix) + '/' + stripSlashes(url)
}

export function stripSlashes(string, position) {
  if (position === 'start') return stripSlashesFromStart(string)
  else if (position === 'end') return stripSlashesFromEnd(string)
  else return stripSlashesFromStart(stripSlashesFromEnd(string))
}

export function stripSlashesFromEnd(string) {
  string = stripSlash(string, 'end')
  return hasSlashAtEnd(string) ? stripSlashes(string, 'end') : string
}

export function stripSlashesFromStart(string) {
  string = stripSlash(string, 'start')
  return hasSlashAtStart(string) ? stripSlashes(string, 'start') : string
}

export function stripSlash(string, position = 'start') {
  if (position === 'start') return stripSlashFromStart(string)
  else if (position === 'end') return stripSlashFromEnd(string)
  else return string
}

export function stripSlashFromEnd(string) {
  return hasSlash(string, 'end') ? string.slice(0, string.length - 1) : string
}

export function stripSlashFromStart(string) {
  return hasSlash(string, 'start') ? string.slice(1, string.length) : string
}

export function addProtocolIfMissing(string) {
  return !string.includes('https://') ? `https://${string}` : string
}

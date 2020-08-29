/*
 * os_win32/wmiquery.h
 *
 * Home page of code is: http://smartmontools.sourceforge.net
 *
 * Copyright (C) 2011 Christian Franke <smartmontools-support@lists.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 *
 * You should have received a copy of the GNU General Public License
 * (for example COPYING); If not, see <http://www.gnu.org/licenses/>.
 *
 */

#ifndef WMIQUERY_H
#define WMIQUERY_H

#define WMIQUERY_H_CVSID "$Id: wmiquery.h 3243 2011-01-19 20:03:47Z chrfranke $"

#ifdef HAVE_WBEMCLI_H
#include <wbemcli.h>
#else
#include "wbemcli_small.h"
#endif

#include <string>

#if !defined(__GNUC__) && !defined(__attribute__)
#define __attribute__(x)  /**/
#endif

/////////////////////////////////////////////////////////////////////////////
// com_bstr

/// Wrapper class for COM BSTR
class com_bstr
{
public:
  /// Construct from string.
  com_bstr(const char * str);

  /// Destructor frees BSTR.
  ~com_bstr()
    { SysFreeString(m_bstr); }

  /// Implicit conversion to BSTR.
  operator BSTR()
    { return m_bstr; }

  /// Convert BSTR back to std::string.
  static bool to_str(const BSTR & bstr, std::string & str);

private:
  BSTR m_bstr;

  com_bstr(const com_bstr &);
  void operator=(const com_bstr &);
};


/////////////////////////////////////////////////////////////////////////////
// com_intf_ptr

/// Wrapper class for COM Interface pointer
template <class T>
class com_intf_ptr
{
public:
  /// Construct empty object
  com_intf_ptr()
    : m_ptr(0) { }

  /// Destructor releases the interface.
  ~com_intf_ptr()
    { reset(); }

  /// Release interface and clear the pointer.
  void reset()
    {
      if (m_ptr) {
        m_ptr->Release(); m_ptr = 0;
      }
    }

  /// Return the pointer.
  T * get()
    { return m_ptr; }

  /// Pointer dereferencing.
  T * operator->()
    { return m_ptr; }

  /// Return address of pointer for replacement.
  T * * replace()
    { reset(); return &m_ptr; }

  /// For (ptr != 0) check.
  operator bool() const
    { return !!m_ptr; }

  /// For (ptr == 0) check.
  bool operator!() const
    { return !m_ptr; }

private:
  T * m_ptr;

  com_intf_ptr(const com_intf_ptr &);
  void operator=(const com_intf_ptr &);
};


/////////////////////////////////////////////////////////////////////////////
// wbem_object

class wbem_enumerator;

/// Wrapper class for IWbemClassObject
class wbem_object
{
public:
  /// Get string representation.
  std::string get_str(const char * name) /*const*/;

private:
  /// Contents is set by wbem_enumerator.
  friend class wbem_enumerator;
  com_intf_ptr<IWbemClassObject> m_intf;
};


/////////////////////////////////////////////////////////////////////////////
// wbem_enumerator

class wbem_services;

/// Wrapper class for IEnumWbemClassObject
class wbem_enumerator
{
public:
  /// Get next object, return false if none or error.
  bool next(wbem_object & obj);

private:
  /// Contents is set by wbem_services.
  friend class wbem_services;
  com_intf_ptr<IEnumWbemClassObject> m_intf;
};


/////////////////////////////////////////////////////////////////////////////
// wbem_services

/// Wrapper class for IWbemServices
class wbem_services
{
public:
  /// Connect to service, return false on error.
  bool connect();

  /// Execute query, get result list.
  /// Return false on error.
  bool vquery(wbem_enumerator & result, const char * qstr, va_list args) /*const*/;

  /// Execute query, get single result object.
  /// Return false on error or result size != 1.
  bool vquery1(wbem_object & obj, const char * qstr, va_list args) /*const*/;

  /// Version of vquery() with printf() formatting.
  bool query(wbem_enumerator & result, const char * qstr, ...) /*const*/
       __attribute__ ((format (printf, 3, 4)));

  /// Version of vquery1() with printf() formatting.
  bool query1(wbem_object & obj, const char * qstr, ...) /*const*/
       __attribute__ ((format (printf, 3, 4)));

private:
  com_intf_ptr<IWbemServices> m_intf;
};

#endif // WMIQUERY_H

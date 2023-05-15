/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

package vfsfs

import (
	"fmt"
	"sync"
	"time"
)

const timeout = 1

var stuckMounts map[string]int
var stuckMux sync.Mutex

type fsCaller struct {
	fsFunc  func(path string) (stats *FsStats, err error)
	paths   []string
	errChan chan error
	outChanStuckUnchecked chan *FsStats
	outChanStuckChecked chan interface{}
	p       *Plugin
}

func (f *fsCaller) executeFunc(path string) {
	stats, err := f.fsFunc(path)

	defer func() {
		stuckMux.Lock()
		stuckMounts[path] = 0
		stuckMux.Unlock()
	}()

	if err != nil {
		f.errChan <- err
		return
	}

	f.outChanStuckUnchecked <- stats
}

func (f *fsCaller) checkNotStuckAndExecute(path string) {
	if isStuck(path) {
		f.outChanStuckChecked <- fmt.Errorf("mount '%s' is unavailable", path)

		return
	}

	go f.executeFunc(path)

	for {
		select {
		case stat := <-f.outChanStuckUnchecked:

			defer func() {
				stuckMux.Lock()
				stuckMounts[path] = 0
				stuckMux.Unlock()
			}()

			f.outChanStuckChecked <- stat

			return
		case err := <-f.errChan:

			defer func() {
				stuckMux.Lock()
				stuckMounts[path] = 0
				stuckMux.Unlock()
			}()

			f.outChanStuckChecked <- err

			return
		case <-time.After(timeout * time.Second):
			stuckMux.Lock()
			stuckMounts[path]++
			stuckMux.Unlock()
			f.outChanStuckChecked <- fmt.Errorf("operation on mount '%s' timed out", path)
		}
	}
}

func (f *fsCaller) run(path string) (stat *FsStats, err error) {

	go f.checkNotStuckAndExecute(path)

	v := <- f.outChanStuckChecked

	switch d := v.(type) {
	case *FsStats:
		return d, nil
	case error:
		return nil, d
	default:
		return nil, fmt.Errorf("Unsupported return type %T", d)
	}
}

func isStuck(path string) bool {
	stuckMux.Lock()
	defer stuckMux.Unlock()
	return stuckMounts[path] > 0
}

func (p *Plugin) newFSCaller(fsFunc func(path string) (stats *FsStats, err error), fsLen int) *fsCaller {
	fc := fsCaller{}
	fc.fsFunc = fsFunc
	fc.errChan = make(chan error, fsLen)
	fc.outChanStuckUnchecked = make(chan *FsStats, fsLen)
	const stuckCheckedLen = 2
	fc.outChanStuckChecked = make(chan interface{}, stuckCheckedLen)
	fc.p = p

	return &fc
}

func init() {
	stuckMounts = make(map[string]int)
}

export interface Ticket {
  id: number;
  ticket_number: string;
  ticket_type: 'INCIDENT' | 'REQUEST' | 'PROBLEM' | 'CHANGE';
  category_id: number;
  subcategory_id?: number;
  requester_id: number;
  assigned_to_id?: number;
  team_id?: number;
  status: 'NEW' | 'ASSIGNED' | 'IN_PROGRESS' | 'PENDING' | 'RESOLVED' | 'CLOSED' | 'CANCELLED';
  priority: 'LOW' | 'MEDIUM' | 'HIGH' | 'URGENT' | 'CRITICAL';
  sla_id?: number;
  due_date?: string;
  subject: string;
  description: string;
  is_security_incident: boolean;
  is_sla_breached: boolean;
  is_escalated: boolean;
  escalation_level: number;
  first_response_at?: string;
  resolved_at?: string;
  resolution_duration?: number;
  resolution_status?: 'RESOLVED' | 'WORKAROUND' | 'CANNOT_REPRODUCE' | 'DUPLICATE';
  resolution_summary?: string;
  root_cause?: string;
  actions_taken?: string;
  preventive_measures?: string;
  satisfaction_rating?: number;
  satisfaction_feedback?: string;
  created_at: string;
  updated_at: string;
  category?: TicketCategory;
  subcategory?: TicketSubcategory;
  requester?: User;
  assigned_to?: User;
  team?: Team;
  comments?: TicketComment[];
  attachments?: TicketAttachment[];
  histories?: TicketHistory[];
  sla_tracking?: TicketSLATracking;
  security_incident?: SecurityIncident;
}

export interface TicketCategory {
  id: number;
  category_name: string;
  category_code: string;
  default_priority: string;
  is_security_related: boolean;
  is_active: boolean;
}

export interface TicketSubcategory {
  id: number;
  category_id: number;
  subcategory_name: string;
  subcategory_code: string;
  is_active: boolean;
}

export interface User {
  id: number;
  employee_id: string;
  username: string;
  email: string;
  first_name: string;
  last_name: string;
  full_name: string;
  role: string;
  department?: string;
  branch?: string;
}

export interface Team {
  id: number;
  team_name: string;
  team_code: string;
}

export interface TicketComment {
  id: number;
  ticket_id: number;
  user_id: number;
  comment_text: string;
  comment_type: 'PUBLIC' | 'INTERNAL';
  time_spent?: number;
  is_visible_to_requester: boolean;
  mentioned_user_ids?: number[];
  created_at: string;
  user?: User;
  attachments?: TicketAttachment[];
}

export interface TicketAttachment {
  id: number;
  ticket_id?: number;
  comment_id?: number;
  file_name: string;
  file_path: string;
  file_type: string;
  file_size: number;
  file_hash: string;
  virus_scan_status: 'PENDING' | 'CLEAN' | 'INFECTED' | 'ERROR';
  is_evidence: boolean;
  description?: string;
  uploaded_by: number;
  created_at: string;
  uploaded_by_user?: User;
}

export interface TicketHistory {
  id: number;
  ticket_id: number;
  user_id?: number;
  action_type: string;
  field_name?: string;
  old_value?: string;
  new_value?: string;
  description?: string;
  metadata?: any;
  created_at: string;
  user?: User;
}

export interface TicketSLATracking {
  id: number;
  ticket_id: number;
  sla_policy_id: number;
  first_response_target_at?: string;
  first_response_actual_at?: string;
  first_response_breached: boolean;
  resolution_target_at?: string;
  resolution_actual_at?: string;
  resolution_breached: boolean;
  overall_sla_status: 'ON_TIME' | 'AT_RISK' | 'BREACHED';
}

export interface SecurityIncident {
  id: number;
  ticket_id: number;
  incident_number: string;
  incident_classification: string;
  attack_vector: string;
  confidentiality_impact: 'NONE' | 'LOW' | 'MEDIUM' | 'HIGH';
  integrity_impact: 'NONE' | 'LOW' | 'MEDIUM' | 'HIGH';
  availability_impact: 'NONE' | 'LOW' | 'MEDIUM' | 'HIGH';
  investigation_status: 'NOT_STARTED' | 'IN_PROGRESS' | 'UNDER_REVIEW' | 'COMPLETED' | 'CLOSED';
  detected_at: string;
  contained_at?: string;
  eradicated_at?: string;
  recovered_at?: string;
  forensic_evidence_collected: boolean;
  evidence_storage_location?: string;
  detection_method?: string;
  affected_assets?: any[];
  root_cause_category?: string;
  root_cause_description?: string;
  immediate_actions_taken?: string;
  remediation_actions?: string;
  preventive_measures?: string;
  requires_regulatory_reporting: boolean;
  regulatory_bodies_notified?: any[];
  customers_notified_at?: string;
  lessons_learned?: string;
  post_incident_review_completed: boolean;
}
